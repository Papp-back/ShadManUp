<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseComment;
use App\Models\CourseSection;
use App\Models\CommentLike;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;




class PaymentController
{
    /**
 * @OA\Get(
 *     path="/payments",
 *     summary="Get Payments with pagination",
 *     tags={"Payment"},
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
 *         name="user_id",
 *         in="query",
 *         description="Filter notifications by user ID",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="only_buy",
 *         in="query",
 *         description="Filter notifications by only buy",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="only_withdraw",
 *         in="query",
 *         description="Filter notifications by only buy",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
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


 public function getPayments(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $user_id = $request->input('user_id');
    $only_buy = $request->input('only_buy');
    $only_withdraw = $request->input('only_withdraw');
    // Start building the query
    $query = Payment::query()->with('section')->with('course');
    if ($user_id) {
        $query->where('user_id', $user_id);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            $q->orWhere('content', 'like', '%' . $search . '%');
            
        });
    }
   
    if ($only_withdraw) {
        $query->where(function ($q) use ($search) {
            $q->where('paytype', 'withdraw');;
        });
    }else{
        if ($only_buy) {
            $query->where(function ($q) use ($search) {
                $q->where('paytype', 'section');
                $q->orWhere('paytype', 'course');
                
            });
        }
    }
    $query->orderBy('id', 'desc');
    // Execute the query and paginate the results
    $payments = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedPayments= $payments->map(function ($payment) {
        return $payment->prettifyAmount()->withJdateHuman();
    });
    return jRWithPagination($payments, $transformedPayments, 200, true, '', []);
}
/**
 * @OA\Post(
 *     path="/payments/{id}/approve",
 *     summary="Approve a payment",
 *     tags={"Payment"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the payment to approve",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Success response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت اعمال شد"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Payment not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="پرداختی یافت نشد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

public function approvePayment($id,Request $request) {
    
    
    $payment=Payment::with('user')->find($id);
    if (!$payment) {
        return jsonResponse([], 400, false, 'پرداختی وجود ندارد .', []);
    }
    if ($payment->paytype!='withdraw') {
        return jsonResponse([], 400, false, 'نوع پرداخت برداشت از حساب نمی باشد.', []);
    }
    $payment->pay=1;
    $q=$payment->save();
   
    if ($q) {
        
        Notification::create([
            'title'=>'واریز به حساب',
            'content'=>withdrawalNotification($payment->user->firstname,'success',$payment->jupdated_at,$payment->card),
            'user_id'=>$payment->user->id,
            'read'=>0
        ]);
    }

    return jsonResponse([], 200, true, 'با موفقیت اعمال شد', []);
}




/**
 * @OA\Post(
 *     path="/payments/{id}/reject",
 *     summary="reject a payment",
 *     tags={"Payment"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the payment to reject",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),

 *     @OA\Response(
 *         response=200,
 *         description="Success response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت اعمال شد"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Payment not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="پرداختی یافت نشد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

public function rejectPayment($id,Request $request) {
    
    
    $payment=Payment::with('user')->find($id);
    if (!$payment) {
        return jsonResponse([], 400, false, 'پرداختی وجود ندارد .', []);
    }
    if ($payment->paytype!='withdraw') {
        return jsonResponse([], 400, false, 'نوع پرداخت برداشت از حساب نمی باشد.', []);
    }
    $payment->pay=0;
    $q=$payment->save();
   
    if ($q) {
        
        Notification::create([
            'title'=>'واریز به حساب',
            'content'=>withdrawalNotification($payment->user->firstname,'error',$payment->jupdated_at,$payment->card),
            'user_id'=>$payment->user->id,
            'read'=>0
        ]);
    }

    return jsonResponse([], 200, true, 'با موفقیت اعمال شد', []);
}
}
