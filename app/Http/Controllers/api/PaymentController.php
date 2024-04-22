<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseComment;
use App\Models\CourseSection;
use App\Models\CommentLike;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;




class PaymentController
{
    public function verifyPayment(Request $request) {
        
        $authority = request()->query('Authority'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $status = request()->query('Status'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $amount = request()->query('amount'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $coupon_id = request()->query('coupon_id'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
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
        $user_wallet=intval($user->wallet);
        $walletExpire = $user->wallet_expire;
        if ($walletExpire && Carbon::now()->lt(Carbon::parse($walletExpire))) {
            $user_wallet_gift=intval($user->wallet_gift);
        } else {
            $user_wallet_gift=0;
        }
        $wallet=$user_wallet+$user_wallet_gift;
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
        if ($payment->StartPay!='wallet' && $response && !$response->success()) {
            $payment->delete();
            return $response->error()->message();
        }

        if($wallet){
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
            $user->save();
            
        }
        if ($payment->StartPay=='wallet') {
            $payment->refId='کیف پول';
            $payment->pay=1;
            $payment->save();
            return 'پرداخت از کیف پول با موفقیت انجام شد.';
        }else{
       
           
            $payment->refId=$response->referenceId();
            $payment->pay=1;
            $payment->save();
            // دریافت هش شماره کارتی که مشتری برای پرداخت استفاده کرده است
            // $response->cardHash();
            
            // دریافت شماره کارتی که مشتری برای پرداخت استفاده کرده است (بصورت ماسک شده)
            // $response->cardPan();
            
            // پرداخت موفقیت آمیز بود
            // دریافت شماره پیگیری تراکنش و انجام امور مربوط به دیتابیس
            return $response->referenceId();
            
        }    
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
