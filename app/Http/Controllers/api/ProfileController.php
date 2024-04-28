<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller\api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Payment;
use App\Models\CourseSection;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller
{
    /**
 * @OA\Post(
 *     path="/profile/save-avatar",
 *     summary="Save user's avatar",
 *     tags={"Profile"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"avatar"},
 *                 @OA\Property(property="avatar", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="avatar", type="string", example="http://example.com/storage/avatars/avatar_1.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or avatar not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="تصویری ارسال نشده است")
 *         )
 *     )
 * )
 */

 public function saveAvatar(Request $request)
 {
     $validator = ValidationFeilds($request, __FUNCTION__);
     if ($validator) {
         return $validator;
     }
 
     // Store the avatar
     if ($request->hasFile('avatar')) {
         $avatar = $request->file('avatar');
         $avatarName = 'avatar_' . auth()->id() . '.' . $avatar->getClientOriginalExtension();
 
         // Delete existing avatar if it exists
         $user = auth('api')->user();
         if ($user->avatar) {
             Storage::disk('public')->delete($user->avatar);
         }
 
         // Store new avatar
         $avatar->storeAs('avatars', $avatarName, 'public');
 
         // Update user's avatar path in the database
         $user->avatar = 'avatars/' . $avatarName;
         $user->save();
         return jsonResponse(['avatar' => url('storage/avatars/' . $avatarName)], 200, true, 'با موفقیت بروزرسانی شد.', []);
     }
     return jsonResponse([], 422, false, 'تصویری ارسال نشده است', []);
 }
 /**
 * @OA\Put(
 *     path="/profile/update",
 *     summary="Update user data",
 *     tags={"Profile"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="firstname", type="string"),
 *                 @OA\Property(property="lastname", type="string"),
 *                 @OA\Property(property="national_code", type="string"),
 *         
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation error message"),
 *          
 *         )
 *     )
 * )
 */

 public function updateUserData(Request $request){
    $validator = ValidationFeilds($request, __FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user = auth('api')->user();

    // Update user data
    $user->update($request->all());
        // Return response
    return jsonResponse([$user], 200, true, 'با موفقیت بروزرسانی شد.', []);
    

 }

       /**
 * @OA\Get(
 *     path="/profile/courses",
 *     summary="Get user purchased course",
*      tags={"Profile"},
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Course")),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

 public function UserCourses(Request $request) {
    $user_id=auth('api')->user()->id;
    $purchasedCourses = Course::whereHas('payments', function ($query) use ($user_id) {
        $query->where('user_id', $user_id)->where('pay', 1);
    })->with(['payments', 'sections' => function ($query) use ($user_id) {
        $query->whereHas('payments', function ($query) use ($user_id) {
            $query->where('user_id', $user_id)->where('pay', 1);
        });
    }])->get();
    
    // Check if pay type is 'course' and include all sections
    $purchasedCourses->each(function ($course) use ($user_id) {
        $price_info=$this->getRemainingSectionFinalPriceSum($course->id,$user_id);
        $course->price=$price_info[0];
        $course->discount=$price_info[1];
        $course->final_price=$price_info[0]-$price_info[1];
        $course->image=url('storage'.$course->image);
        // Get the payment record for the user and course
        $payment = $course->payments->where('user_id', $user_id)->first();
        if ($payment && $payment->paytype === 'course') {
            // Get all sections for the course
            $allSections = CourseSection::where('course_id', $course->id)->get();
            // echo json_encode($allSections);
            // Add the sections to the course
            $course->purchased_sections = $allSections;
        }else{
            $course->purchased_sections= $course->sections;
        }
        unset($course->payments);
        unset($course->sections);
        return $course->prettifyPrice();
    });

  
    return jsonResponse($purchasedCourses,200, true, '', []);
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


       /**
 * @OA\Get(
 *     path="/profile/payments",
 *     summary="Get user payments list",
*      tags={"Profile"},
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */


public function paymentHistory(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch the payment history for the user
        $payments = Payment::with('course')->with('section')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->map(function ($payment) {
            if ($payment->paytype=='withdraw' ) {
                if ($payment->pay==1) {
                    $payment->status='واریز شده به حساب';
                }else{
                    $payment->status='درحال بررسی';
                }
            }else{
                $payment->status='';
            }
            return $payment;
        });

        // Return the payment history as a response
        return jsonResponse($payments,200, true, '', []);
    }



}