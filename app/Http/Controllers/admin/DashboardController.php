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
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class DashboardController
{

    /**
 * @OA\Get(
 *     path="/dashboard/statistics",
 *     summary="Get statistics",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="today_payments_count", type="string", example="10,000 تومان"),
 *             @OA\Property(property="payments_amount", type="string", example="100,000 تومان"),
 *             @OA\Property(property="courseCount", type="integer", example=10),
 *             @OA\Property(property="userCount", type="integer", example=100),
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function statics(){
        $today=Carbon::today();
        $todayPaymentsCount = Payment::where(function ($q) {
            $q->where('paytype','section');
            $q->orWhere('paytype','course');
        })->where('pay',1)->whereDate('created_at', $today)->sum('amount');

        // Total amount of payments made today
        $PaymentsAmount = Payment::where(function ($q) {
            $q->where('paytype','section');
            $q->orWhere('paytype','course');
        })->where('pay',1)->sum('amount');
        // Get count of courses
        $courseCount = Course::count();

        // Get count of users
        $userCount = User::count();

        return jsonResponse([
            'today_payments_count' =>number_format(floatval($todayPaymentsCount), 0, '.', ',').' تومان' ,
            'payments_amount' =>number_format(floatval($PaymentsAmount), 0, '.', ',').' تومان' ,
            'courseCount' => $courseCount,
            'userCount' => $userCount,
        ], 200, true, '', []);
    }


/**
 * @OA\Get(
 *     path="/dashboard/latest-notifications",
 *     summary="Get latest notifications",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Notification")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */


    public function latestNotifications(){
         // Get count of users
         $lnotif = Notification::with('user')->latest()->take(5)->get();
         $lnotif->map(function ($l) {

            return $l->withJdateHuman();
        });
         return jsonResponse($lnotif, 200, true, '', []);
    }

    /**
 * @OA\Get(
 *     path="/dashboard/latest-comments",
 *     summary="Get latest Comments",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/CommentCourse")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function latestComments(){
         // Get count of users
         $lnotif = CourseComment::with('user')->latest()->take(5)->get();
         $lnotif->map(function ($l) {

            return $l->withJdateHuman();
        });
         return jsonResponse($lnotif, 200, true, '', []);
    }


        /**
 * @OA\Get(
 *     path="/dashboard/latest-orders",
 *     summary="Get latest Payments",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Payment")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function latestOrders(){
         // Get count of users
         $lnotif = Payment::with('user')->with('course')->with('section')->where(function ($q) {
            $q->where('paytype','section');
            $q->orWhere('paytype','course');
        })->latest()->take(5)->get();
        $lnotif->map(function ($l) {

            return $l->withJdateHuman();
        });

         return jsonResponse($lnotif, 200, true, '', []);
    }


    /**
 * @OA\Get(
 *     path="/dashboard/order-chart",
 *     summary="Get payments chart data for the last 30 days",
 *     tags={"Dashboard"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date", type="string", format="date", description="Date in YYYY/MM/DD format"),
 *                 @OA\Property(property="total_pay", type="number", format="float", description="Total payment amount for the date")
 *             )
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */



    public function paymentsChart(Request $request)
    {
        $dateRange = collect(range(0, 30))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->toDateString();
        });
        
        // Retrieve payments for the 30-day period
        $payments = DB::table('payments')
            ->rightJoin(
                DB::raw('(' . $dateRange->map(function ($date) {
                    return "SELECT '$date' AS date";
                })->implode(' UNION ALL ') . ') AS dates'),
                function ($join) {
                    $join->on('dates.date', '=', DB::raw('DATE(payments.created_at)'));
                }
            )
            ->selectRaw('dates.date, COALESCE(SUM(payments.amount), 0) as total_pay')
            ->groupBy('dates.date')
            ->orderBy('dates.date')
            ->get();
        $payments->transform(function ($payment) {
                $payment->date = Jalalian::forge($payment->date)->format('Y/m/d');
                return $payment;
            });
        return jsonResponse($payments, 200, true, '', []);
    }
}
