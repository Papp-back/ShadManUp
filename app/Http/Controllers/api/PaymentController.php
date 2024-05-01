<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseComment;
use App\Models\CourseSection;
use App\Models\CommentLike;
use App\Models\Payment;
use App\Models\ReferralPay;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;



class PaymentController
{
    public function verifyPayment(Request $request) {
        
        $authority = request()->query('Authority'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $status = request()->query('Status'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $amount = request()->query('amount'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $coupon_id = request()->query('coupon_id'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $wallet_use = request()->query('wallet_use'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $payment=Payment::where('Authority',$authority)->where('pay',0)->first();
        if (!$payment) {
            return response()->json(['message' => "پرداختی وجود ندارد."], 404);
        }
        $section_id=$payment->section_id;
        $course_id=$payment->course_id;
        $paytype=$payment->paytype;
        $user_id=$payment->user_id;
        $user=User::find($user_id);

        if ($paytype=='course') {
            $course=Course::find($course_id);
            $price_info=$this->getRemainingSectionFinalPriceSum($course_id,$user_id);
            $can_purchase=$price_info[3];
            
            if (!$can_purchase) {
                return jsonResponse([], 400, false, 'دوره توسط کاربر خریداری شده است.', []);
            }
            $final_price=$price_info[2]?$price_info[2]:floatval($course->price)-floatval($course->discount);
        }elseif($paytype=='section' && $section_id){
            $can_purchase=Payment::where('section_id',$section_id)->where('user_id',$user_id)->first();
           
            if (!$can_purchase) {
                return jsonResponse([], 400, false, 'جلسه دوره توسط کاربر خریداری شده است.', []);
            }
            $courseSection=CourseSection::find($section_id);
            $final_price=floatval($courseSection->price)-floatval($courseSection->discount);
        }
        if ($wallet_use) {
            $user_wallet=intval($user->wallet);
            $walletExpire = $user->wallet_expire;
            $user_wallet_pay_expire = $user->wallet_pay_expire;
            if ($walletExpire && Carbon::now()->lt(Carbon::parse($walletExpire))) {
                $user_wallet_gift=floatval($user->wallet_gift);
            } else {
                $user_wallet_gift=0;
            }
            if ($user_wallet_pay_expire && Carbon::now()->lt(Carbon::parse($user_wallet_pay_expire))) {
                $user_wallet_pay=floatval($user->wallet_pay);
            } else {
                $user_wallet_pay=0;
            }
            $wallet=$user_wallet+$user_wallet_gift+$user_wallet_pay;
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
        $response = zarinpal()
        ->amount($amount)
        ->verification()
        ->authority($authority)
        ->send();
        // if ($payment->StartPay!='wallet' && $response && !$response->success()) {
        //     $payment->delete();
        //     return $response->error()->message();
        // }
        if ($wallet_use) {
            if($wallet){
                if ($final_price < $user_wallet_pay) {
                    $user_wallet_pay -= $final_price;
                    $user->wallet_pay=$user_wallet_pay;
                }else
                {
                    $final_price -= $user_wallet_pay;
                    $user->wallet_pay=0;
                    if ($final_price < $user_wallet_gift) {
                        $user_wallet_gift -= $final_price;
                        $user->wallet_gift=$user_wallet_gift;
                    } else {
                        $final_price -= $user_wallet_gift;
                        if ($final_price<$user_wallet) {
                            $user_wallet -= $final_price;
                        }else{
                            $user_wallet=0;
                        }
                        $user->wallet_gift=0;
                        $user->wallet=$user_wallet;
                    }
                }
              
                $user->save();
                
            }
        }
        $d_amount=($amount*5)/100;
        if (env('Business_partner_ID')) {
            $Business_id = env('Business_partner_ID');
            $b_user=User::find($Business_id);
            $b_user->wallet=floatval($b_user->wallet)+$d_amount;
            $b_user->save();
            $currentDateTime = Jalalian::now();
            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
            Notification::create([
                'title'=>'دریافت پورسانت شریک تجاری ',
                'content'=>"کیف پول شریک تجاری (آقای مهدی باصری) واریز شد. در تاریخ {$formattedDateTime} ، 5 درصد از مبلغ فروش محصول به شماره شناسه {$payment->id} به مبلغ {$d_amount} تومان",
                'user_id'=>env('Admin_ID'),
                'read'=>0
            ]);
            Notification::create([
                'title'=>'دریافت پورسانت شریک تجاری ',
                'content'=>"کیف پول شریک تجاری (آقای مهدی باصری) واریز شد. در تاریخ {$formattedDateTime} ، 5 درصد از مبلغ فروش محصول به شماره شناسه {$payment->id} به مبلغ {$d_amount} تومان",
                'user_id'=>env('Business_partner_ID'),
                'read'=>0
            ]);
        }
        if (User::where('referrer',$user->referral)->first()) {
            $user_r=User::find($user->id);
            $user_r->wallet_gift=floatval($user->wallet_gift)+$d_amount;
            $user_r->wallet_expire=Carbon::now()->addDays(90);
            $user_r->save();
            Notification::create([
                'title'=>'هدیه خرید',
                'content'=>" با تشکر از خرید شما . 5 درصد از خرید شما به عنوان هدیه به کیف پول شما برای خریدهای بعدی واریز شد. مهلت استفاده از آن 90 روز می باشد. ",
                'user_id'=>$user->id,
                'read'=>0
            ]);
            Payment::create([
                'card'=>'',
                'Authority'=>'',
                'pay'=>1,
                'StartPay'=>'',
                'course_id'=>0,
                'user_id'=>$user_r->id,
                'paytype'=>'gift',
                'Amount'=>$d_amount,
                'section_id'=>null,
                'desc'=>'هدیه خرید',
            ]);
        }
        if ($user->referrer) {
            $user_referrer=User::where('referral',$user->referrer)->first();
            $user_referrer->wallet_pay=floatval($user_referrer->wallet_pay)+$d_amount;
            $user_referrer->wallet_pay_expire=Carbon::now()->addDays(90);
            $currentDateTime = Jalalian::now();
            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
            ReferralPay::create([
                'user_id'=>$user_referrer->id,
                'payment_id'=>$payment->id,
                'percent'=>5,
                'description'=>"در تاریخ {$formattedDateTime} ،5درصد پورسانت خرید محصول توسط دوست شما ({$user->firstname} {$user->lastname}) به مبلغ {$d_amount} تومان به کیف پول شما واریز گردید.لازم به ذکر است که زمان برداشت این مبلغ از کیف پول 90 روز می باشد."
            ]);
            Notification::create([
                'title'=>'هدیه خرید دوستان',
                'content'=>"در تاریخ {$formattedDateTime} ،5درصد پورسانت خرید محصول توسط دوست شما ({$user->firstname} {$user->lastname}) به مبلغ {$d_amount} تومان به کیف پول شما واریز گردید.لازم به ذکر است که زمان برداشت این مبلغ از کیف پول 90 روز می باشد.",
                'user_id'=>$user_referrer->id,
                'read'=>0
            ]);
            Payment::create([
                'card'=>'',
                'Authority'=>'',
                'StartPay'=>'',
                'course_id'=>0,
                'user_id'=>$user_referrer->id,
                'paytype'=>'gift',
                'Amount'=>$d_amount,
                'section_id'=>null,
                'pay'=>1,
                'desc'=>'هدیه خرید دوستان',
            ]);
            $user_referrer->save();
            $user=User::find($user->id);
            $p=($amount*10)/100;
            $user->wallet_gift=floatval($user->wallet_gift)+$p;
            $user->wallet_expire=Carbon::now()->addDays(90);
            $user->save();
            Notification::create([
                'title'=>'هدیه خرید',
                'content'=>" با تشکر از خرید شما . 10 درصد از خرید شما به عنوان هدیه به کیف پول شما برای خریدهای بعدی واریز شد. مهلت استفاده از آن 90 روز می باشد. ",
                'user_id'=>$user->id,
                'read'=>0
            ]);
            Payment::create([
                'card'=>'',
                'Authority'=>'',
                'StartPay'=>'',
                'course_id'=>0,
                'user_id'=>$user->id,
                'paytype'=>'gift',
                'Amount'=>$p,
                'pay'=>1,
                'section_id'=>null,
                'desc'=>'هدیه خرید',
            ]);
        }
        if ($payment->paytype=='section') {
            $Course=CourseSection::find($payment->section_id);
        }else{
            $Course=Course::find($payment->course_id);
        }
        
        Notification::create([
            'title'=>'خرید محصول',
            'content'=>"با تشکر از خرید شما.محصول با نام {$Course->title} با موفقیت خریداری شد.شما میتوانید در پروفایل کاربری خود محصولاتی که خریده اید را مشاهده کنید.",
            'user_id'=>$user->id,
            'read'=>0
        ]);
        if ($payment->StartPay=='wallet') {
            $payment->refId='کیف پول';
            $payment->pay=1;
            $payment->save();
            return 'پرداخت از کیف پول با موفقیت انجام شد.';
        }else{
           
            // $payment->refId=$response->referenceId();
            $payment->refId='ascascasc';
            $payment->pay=1;
            $payment->save();
            // دریافت هش شماره کارتی که مشتری برای پرداخت استفاده کرده است
            // $response->cardHash();
            
            // دریافت شماره کارتی که مشتری برای پرداخت استفاده کرده است (بصورت ماسک شده)
            // $response->cardPan();
            
            // پرداخت موفقیت آمیز بود
            // دریافت شماره پیگیری تراکنش و انجام امور مربوط به دیتابیس
            return 'تراکنش با موفقیت انجام شد.';
            
        }    
    }
    /**
 * @OA\Post(
 *     path="/wallet/deposit",
 *     summary="Deposit money to the user's wallet",
 *     tags={"Wallet"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Amount and card information",
 *         @OA\JsonContent(
 *             required={"Amount", "cardNumber"},
 *             @OA\Property(property="Amount", type="number",example="1000", description="The amount to deposit(toman)"),
 *             @OA\Property(property="description", type="string", description="Optional description for the deposit")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="Amount", type="number", description="The deposited amount"),
 *             @OA\Property(property="StartPay", type="string", description="The URL for initiating the payment process"),
 *             @OA\Property(property="message", type="string", description="Success message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", description="Error message")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

    public function deposit(Request $request){
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        $Amount=$request->input('Amount');
        $description=$request->input('description');
        $user_id=auth('api')->user()->id;
        $user=User::find($user_id);

        $callBackUrl=url('api/v1/wallet/verify'. '?' . http_build_query(['amount' => $Amount]));
        $res = zarinpal()
                        ->amount($Amount) // مبلغ تراکنش
                        ->request()
                        ->description('Payment User '.auth('api')->user()->id) // توضیحات تراکنش
                        ->callbackUrl($callBackUrl) // آدرس برگشت پس از پرداخت
                        // ->mobile(auth('api')->user()->user_) // شماره موبایل مشتری - اختیاری
                        // ->email('name@domain.com') // ایمیل مشتری - اختیاری
                        ->send();
        if (!$res->success()) {
            return jsonResponse([], 400, false, $res->error()->message(), []);
        }
        $paymnet=Payment::create([
            'Authority'=>$res->authority(),
            'StartPay'=>$res->url(),
            'course_id'=>0,
            'user_id'=>$user_id,
            'paytype'=>'deposit',
            'Amount'=>$Amount,
            'section_id'=>null,
            'desc'=>$description??null,

        ]);   
        return response()->json([
            'Amount'=>$Amount,
            'StartPay'=>$res->url(),
            'message'=>'success'
        ]);
    }
    public function walletverify(Request $request) {
        
        $authority = request()->query('Authority'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $payment_id = request()->query('payment_id'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $amount = request()->query('amount'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
       
        $payment=Payment::where('Authority',$authority)->where('pay',0)->first();
        
        if (!$payment) {
            return response()->json(['message' => "پرداختی وجود ندارد."], 404);
        }
        $user_id=$payment->user_id;
        $user=User::find($user_id);
        $response = zarinpal()
        ->amount($amount)
        ->verification()
        ->authority($authority)
        ->send();
        if (!$response->success()) {
            $payment->delete();
            return $response->error()->message();
        }
        $user_wallet=floatval($user->wallet)+floatval($amount);
        $user->wallet=$user_wallet;
        $user->save();
        $payment->refId=$response->referenceId();
        $payment->pay=1;
        $payment->save();
        if ($payment) {
            $currentDateTime = Jalalian::now();
            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
            Notification::create([
                'title'=>'واریز به کیف پول',
                'content'=>"{$user->firstname} عزیز در تاریخ {$formattedDateTime} کیف  پول شما به مبلغ {$amount} تومان با موفقیت شارژ شد.",
                'user_id'=>$user->id,
                'read'=>0
            ]);
        }
        return 'پرداخت  با موفقیت انجام شد.';
      
         
    }
     /**
 * @OA\Post(
 *     path="/wallet/withdraw",
 *     summary="withdraw money from the user's wallet",
 *     tags={"Wallet"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Amount and card information",
 *         @OA\JsonContent(
 *             required={"Amount", "cardNumber"},
 *             @OA\Property(property="Amount", type="number",example="1000", description="The amount to deposit(toman)"),
 *             @OA\Property(property="cardNumber", type="string", description="The user's card number"),
 *             @OA\Property(property="description", type="string", description="Optional description for the deposit")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="Amount", type="number", description="The deposited amount"),
 *             @OA\Property(property="StartPay", type="string", description="The URL for initiating the payment process"),
 *             @OA\Property(property="message", type="string", description="Success message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", description="Error message")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function withdraw(Request $request){
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        $Amount=$request->input('Amount');
        $cardNumber=$request->input('cardNumber');
        $description=$request->input('description');
        $user_id=auth('api')->user()->id;
        $user=User::find($user_id);
        if (floatval($Amount)<100000) {
            return jsonResponse([], 422, false,'مبلغ درخواستی نباید کمتر از 100000 تومان باشد.', []);
        }
        if (floatval($user->wallet)<floatval($Amount)) {
            return jsonResponse([], 422, false,'مبلغ درخواستی از موجودی کیف پول بیشتر است .', []);
        }

        
        $payment=Payment::create([
            'card'=>$cardNumber,
            'Authority'=>'',
            'StartPay'=>'',
            'course_id'=>0,
            'user_id'=>$user_id,
            'paytype'=>'withdraw',
            'Amount'=>$Amount,
            'section_id'=>null,
            'desc'=>$description??null,
        ]);
        if ($payment) {
            $currentDateTime = Jalalian::now();
            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
            Notification::create([
                'title'=>'درخواست برداشت از کیف پول',
                'content'=>withdrawalNotification($user->firstname,'',$formattedDateTime,$cardNumber),
                'user_id'=>$user->id,
                'read'=>0
            ]);
        }
        $user->wallet= floatval($user->wallet)-floatval($Amount);
        $user->save();
        return jsonResponse([], 200, true,'با موفقیت ثبت شد.', []);
       
    }
    private function getRemainingSectionFinalPriceSum($courseId, $userId) {
        // Retrieve IDs of all sections of the course
        $allSections = CourseSection::where('course_id', $courseId)->get();
        
        // Retrieve IDs of purchased sections by the user
        $purchasedSectionIds = Payment::where('course_id', $courseId)
            ->where('user_id', $userId)->where('pay',1)
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
