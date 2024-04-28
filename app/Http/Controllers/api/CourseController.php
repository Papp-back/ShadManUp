<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseComment;
use App\Models\CommentLike;
use App\Models\CourseSection;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;


class CourseController
{
        /**
 * @OA\Get(
 *     path="/courses",
 *     summary="Get courses with pagination",
 *     tags={"Course"},
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Number of items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="category_id",
 *         in="query",
 *         description="category_id query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Course")),
 *             @OA\Property(property="links", type="object", ref="#/components/schemas/PaginationLinks"),
 *             @OA\Property(property="meta", type="object", ref="#/components/schemas/PaginationMeta"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=422),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation error"),
 *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

 public function getCourses(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $category_id = $request->input('category_id');
    // Start building the query
    $query = Course::query()->with('category')->with('sections')->with('comments');
    if ($category_id) {
        $query->where('category_id', $category_id);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            
        });
    }
    $user_id=auth('api')->user()->id;
    // Execute the query and paginate the results
    $courses = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedCourses = $courses->map(function ($course,$index) use ($user_id){
        $price_info=$this->getRemainingSectionFinalPriceSum($course->id,$user_id);
        $course->price=$price_info[0];
        $course->discount=$price_info[1];
        $course->final_price=$price_info[0]-$price_info[1];
        $can_purchase=$price_info[3];
        $payment=Payment::where('course_id',$course->id)->where('user_id',$user_id)->where('paytype','course')->where('pay',1)->first();
        if ($payment) {
            $can_purchase=0;
        }
        $course->available_for_purchase=$can_purchase;
        $course->status=!$can_purchase?'bought':'';
        $course->status_info=!$can_purchase?'خریداری شده':'خریداری نشده';
        $course->image=url('storage/'.$course->image);
        if (isset($course->sections[0])) {
            $course->sessions_count = $course->sections[0]->sessions()->count(); // Count sessions
            $totalDurationMinutes = $course->sections[0]->sessions()->sum('duration_minutes'); // Sum duration_minutes
            $course->total_duration_time = convertToTime($totalDurationMinutes); // Convert to human-readable time
            if ($totalDurationMinutes) {
               
                $course->sections[$index]->sessions->map(function ($session) {
                    // Convert duration_minutes to HH:MM:SS format
                    $session->duration_minutes = convertToTime($session->duration_minutes);
                    $session->file_size = formatFileSize($session->file_size);
                    return $session;
                });
            }
            $course->sections->each(function ($section) use ($can_purchase,$user_id) {
                
                if (!$can_purchase) {
                    $section->available_for_purchase=0;
                    $section->status='bought';
                    $section->status_info='خریداری شده';
                }else{

                    $section_payment=Payment::where('section_id',$section->id)->where('user_id',$user_id)->where('pay',1)->first();
                    $section->available_for_purchase=$section_payment?0:1;
                    $section->status=$section_payment?'bought':"";
                    $section->status_info=$section_payment?'خریداری شده':"";
                }
                return $section->prettifyPrice();
            });
            
            
        } else {
            $course->sessions_count = "0";
            $course->total_duration_time = '0';
        }
        $course->comments = $course->comments->map(function ($comment) use ($user_id) {
            // Check if the comment has likes
            if ($comment->likes) {
                $comment->likes->each(function ($like) use ($comment, $user_id) {
                    // Check if the user has liked this comment
                    $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                     return $like; 
                });
            }
            return $comment->withJdateHuman();
        });
        $course->comments_count = $course->comments()->count()??0;
       
        return $course->prettifyPrice()->withJdateHuman();
    });

  
    return jRWithPagination($courses, $transformedCourses, 200, true, '', []);
}

/**
* @OA\Get(
*     path="/courses/{id}",
*     summary="Retrieve a single course by ID",
*     tags={"Course"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the course",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/Course"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example=""),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="دروه وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function singleCourse($id,Request $request) {
    $course = Course::with('category')->with('sections')->find($id);
    $user_id=auth('api')->user()->id;
    if (!$course) {
        return jsonResponse([], 404, false, 'دروه وجود ندارد .', []);
    }
    $payment=Payment::where('course_id',$id)->where('user_id',$user_id)->where('paytype','course')->where('pay',1)->first();
    $price_info=$this->getRemainingSectionFinalPriceSum($course->id,$user_id);
    $course->price=$price_info[0];
    $course->discount=$price_info[1];
    $course->final_price=$price_info[0]-$price_info[1];
    $can_purchase=$price_info[3];
    if ($payment) {
        $can_purchase=0;
    }
    $course->available_for_purchase=$can_purchase;
    $course->status=!$can_purchase?'bought':'';
    $course->status_info=!$can_purchase?'خریداری شده':'خریداری نشده';
    if (isset($course->sections[0])) {
        $course->sessions_count = $course->sections[0]->sessions()->count(); // Count sessions
        $totalDurationMinutes = $course->sections[0]->sessions()->sum('duration_minutes'); // Sum duration_minutes
        $course->total_duration_time = convertToTime($totalDurationMinutes); // Convert to human-readable time
        if ($totalDurationMinutes) {
            $course->sections->map(function ($sec){
                $sec->sessions->map(function ($session) {
                    $session->duration_minutes = convertToTime($session->duration_minutes);
                    $session->file_size = formatFileSize($session->file_size);
                    return $session;
                });
            });
        }
        $course->sections->each(function ($section) use ($can_purchase,$user_id){
            if (!$can_purchase) {
                $section->available_for_purchase=0;
                $section->status='bought';
                $section->status_info='خریداری شده';
            }else{
                $section_payment=Payment::where('section_id',$section->id)->where('user_id',$user_id)->where('paytype','section')->where('pay',1)->first();
                $section->available_for_purchase=$section_payment?0:1;
                $section->status=$section_payment?'bought':"";
                $section->status_info=$section_payment?'خریداری شده':"";
            }
            return $section->prettifyPrice();
        });
    } else {
        $course->sessions_count = 0;
        $course->total_duration_time = '0';
    }
    
    
    $course->comments = $course->comments->map(function ($comment) use ($user_id) {
        // Check if the comment has likes
        if ($comment->likes) {
            $comment->likes->each(function ($like) use ($comment, $user_id) {
                // Check if the user has liked this comment
                $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                 return $like; 
            });
        }
        return $comment->withJdateHuman();
    });
    $course->comments_count = $course->comments()->count()??0;
    $course->image=url('storage/'.$course->image);
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
}



/**
* @OA\Get(
*     path="/courses/{id}/comments",
*     summary="Retrieve comments of course by ID",
*     tags={"Course"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the course",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Comments of Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/CommentCourse"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example=""),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Comments of Course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="دروه وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/


public function getCommentsCourse($id,Request $request) {

    $user_id=auth('api')->user()->id;
    $comments = CourseComment::with('likes')->with('user')->withCount('likes')->where('course_id',$id)->get();
    $comments= $comments->map(function ($comment) use ($user_id) {
        // Check if the comment has likes
        if ($comment->likes) {
            $comment->likes->each(function ($like) use ($comment, $user_id) {
                // Check if the user has liked this comment
                $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                 return $like; 
            });
        }
        return $comment->withJdateHuman();
    });
    
    return jsonResponse($comments, 200, true, '', []);
}


/**
 * @OA\Post(
 *      path="/courses/{id}/comments",
 *     summary="Store a new comment for a course",
 *     tags={"Course"},
 *       @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the course",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"comment"},
 *             @OA\Property(property="comment", type="integer", example="Lorem ipsum dolor sit amet, consectetur adip")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *              @OA\Property(property="data", type="object", ref="#/components/schemas/CommentCourse"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت ایجاد شد ."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="object", ref="#/components/schemas/ValidationError"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="ValidationError",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items()),
 *             @OA\Property(property="status", type="integer", example=400),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="شما یک نظر برای این دوره ثبت کرده اید."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */
public function setCommentsCourse($id,Request $request) {
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user_id=auth('api')->user()->id;
    $comment_user = CourseComment::where('course_id',$id)->where('user_id',$user_id)->first();
    if ($comment_user) {
        return jsonResponse([], 400, false, 'شما یک نظر برای این دوره ثبت کرده اید.', []);
    }
    $commentData=$request->all();
    $commentData['user_id']=$user_id;
    $commentData['course_id']=$id;
    $comment=CourseComment::create($commentData);
    
    return jsonResponse($comment, 200, true, '', []);
}
/**
 * @OA\Post(
 *      path="/courses/commentlike",
 *     summary="Store a new comment like for a course",
 *     tags={"Course"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"comment_id"},
 *             @OA\Property(property="comment_id", type="integer", example=1),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment liked successfully",
 *         @OA\JsonContent(
 *             type="object",
 *              @OA\Property(property="data", type="object", ref="#/components/schemas/CommentCourse"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت ثبت شد ."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="object", ref="#/components/schemas/ValidationError"),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */
public function setCommentLikeCourse(Request $request) {
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user_id=auth('api')->user()->id;
    $comment_id=$request->input('comment_id');
    $comment_user = CommentLike::where('course_comment_id',$comment_id)->where('user_id',$user_id)->first();
    if (!$comment_user) {
        $commentData=$request->all();
        $commentData['course_comment_id']=$comment_id;
        $commentData['user_id']=$user_id;
        $comment=CommentLike::create($commentData);
    }
    
    
    return jsonResponse([], 200, true, 'با موفقیت ثبت شد.', []);
}
/**
 * @OA\Post(
 *     path="/courses/{id}/payment",
 *     summary="Set payment for course(or section)",
 *     tags={"Course"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the course",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Payment details",
 *         @OA\JsonContent(
 *             required={"paytype", "copoun_id"},
 *             @OA\Property(property="paytype", type="string",example="course", description="Type of payment (course or section)"),
 *             @OA\Property(property="copoun_id", type="integer", description="ID of the coupon, if any"),
 *             @OA\Property(property="section_id", type="integer", description="ID of the course section, if paying for a section"),
 *             @OA\Property(property="wallet_use", type="integer", description="ID of the course section, if user want use wallet")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="StartPay", type="string", description="Payment start URL"),
 *             @OA\Property(property="message", type="string", description="Message indicating success")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="object", ref="#/components/schemas/ValidationError"),
 *         ),
 *     ),
 *      security={{"bearerAuth": {}}},
 * )
 */

public function setpaymentCourse($id,Request $request){
    $payment = Payment::where('course_id', $id)->whereNull('section_id')->where('pay', 1)->first();
    if ($payment) {
        return jsonResponse([], 400, false, 'دوره توسط کاربر خریداری شده است.', []);
    }
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $couponId=$request->input('copoun_id');
    $section_id=$request->input('section_id');
    $paytype=$request->input('paytype');
    $wallet_use=$request->input('wallet_use');
    $user_id=auth('api')->user()->id;
    $user=User::find($user_id);
    if ($paytype=='course') {
        $course=Course::find($id);
        $price_info=$this->getRemainingSectionFinalPriceSum($id,$user_id);
        $can_purchase=$price_info[3];
        if (!$can_purchase) {
            return jsonResponse([], 400, false, 'دوره توسط کاربر خریداری شده است.', []);
        }
        $final_price=$price_info[2]?$price_info[2]:floatval($course->price)-floatval($course->discount);
    }elseif($paytype=='section' && $section_id){
        $can_purchase=Payment::where('section_id',$section_id)->where('user_id',$user_id)->first();
        if ($can_purchase) {
            return jsonResponse([], 400, false, 'جلسه دوره توسط کاربر خریداری شده است.', []);
        }
        $courseSection=CourseSection::find($section_id);
        $final_price=floatval($courseSection->price)-floatval($courseSection->discount);
    }
    if ($wallet_use) {
        $user_wallet=intval($user->wallet);
        $walletExpire = $user->wallet_expire;
        if ($walletExpire && Carbon::parse($walletExpire)->isFuture()) {
            $user_wallet_gift=intval($user->wallet_gift);
        } else {
            $user_wallet_gift=0;
        }
        $wallet=$user_wallet+$user_wallet_gift;
    }
    
    // if ($couponId) {
    //     $coupon =$results = DB::table('coupons')
    //     ->where('expired_at', '>', now())
    //     ->where('id', $couponId)
    //     ->where('status', 'enable')
    //     ->first();
    //     if ($coupon) {
    //         if ($coupon->type=='percent') {
    //             $final_price-= ($final_price*$coupon->percent)/100;
    //         }else{
    //             $final_price-= $coupon->percent;
    //         }
    //     }
    // }
    
    $authority=$this->generateRandomString();

    
    if ($wallet_use) {
        if($final_price<$wallet){
    
            $paymnet=Payment::create([
            'paytype'=>$paytype,
            'course_id'=>$id,
            'user_id'=>$user_id,
            'Amount'=>$final_price,
            'section_id'=>$paytype=='course'?null:$section_id,
            'Authority'=>$authority,
            'StartPay'=>'wallet',
    
            ]); 
            return response()->json([
            'Amount'=>$final_price,
            'StartPay'=>url('api/v1/payment/verify'. '?' . http_build_query(['Authority'=>$authority,'amount' => $final_price,'coupon_id'=>$couponId,'section_id'=>$section_id])),
            'message'=>'success'
        ]);  
        }
        if($wallet){
            $final_price-=$wallet;
        }
    }
   
    
    $callBackUrl=url('api/v1/payment/verify'. '?' . http_build_query(['amount' => $final_price,'coupon_id'=>$couponId,'section_id'=>$section_id,'wallet_use'=>$wallet_use]));
    $res = zarinpal()
                    ->amount($final_price) // مبلغ تراکنش
                    ->request()
                    ->description('Payment User '.auth('api')->user()->id) // توضیحات تراکنش
                    ->callbackUrl($callBackUrl) // آدرس برگشت پس از پرداخت
                    // ->mobile(auth('api')->user()->user_) // شماره موبایل مشتری - اختیاری
                    // ->email('name@domain.com') // ایمیل مشتری - اختیاری
                    ->send();
    if (!$res->success()) {
        return $res->error()->message();
    }
    $paymnet=Payment::create([
        'coupon_id'=>$couponId,
        'Authority'=>$res->authority(),
        'StartPay'=>$res->url(),
        'course_id'=>$id,
        'user_id'=>$user_id,
        'paytype'=>$paytype,
        'Amount'=>$final_price,
        'section_id'=>$paytype=='course'?null:$section_id,
    ]);   
    return response()->json([
        'Amount'=>$final_price,
        'StartPay'=>$res->url(),
        'message'=>'success'
    ]);

}

private function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}



private function getRemainingSectionFinalPriceSum($courseId, $userId) {
    // Retrieve IDs of all sections of the course
    $allSections = CourseSection::where('course_id', $courseId)->get();
    
    // Retrieve IDs of purchased sections by the user
    $purchasedSectionIds = Payment::where('course_id', $courseId)
        ->where('user_id', $userId)
        ->pluck('section_id')
        ->toArray();
    
    // Calculate the sum of the final prices of remaining sections
    $remainingFinalPriceSum = 0;
    $allSectionsPriceSum = 0;
    $allSectionsDiscountSum = 0;
    foreach ($allSections as $section) {
        $allSectionsPriceSum+=$section->price;
        $allSectionsDiscountSum+=$section->discount;
        if (!in_array($section->id, $purchasedSectionIds)) {
            $remainingFinalPriceSum += (floatval($section->price)-floatval($section->discount));
        }
    }
    $can_purchase=1;
    if (!$remainingFinalPriceSum) {
        $can_purchase=0;
    }
    
    return [$allSectionsPriceSum,$allSectionsDiscountSum,$remainingFinalPriceSum,$can_purchase];
}





}