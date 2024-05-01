<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;



class AuthController
{
        /**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Admin login and get Admin Data",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"mobile_number","password"},
 *             @OA\Property(property="mobile_number", type="string", format="phone number", example="1234567890"),
 *             @OA\Property(property="password", type="string", example="*****")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="status", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or failure to send SMS",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="شما اجازه دسترسی ندارید ."),
 *             @OA\Property(property="errors", type="array", @OA\Items())
 *         )
 *     ),
 * )
 */
public function Adminlogin(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    // Check if the admin exists
    $admin = User::where('cellphone', $request->input('mobile_number'))->where('role', 1)->first();
   
    if (!$admin) {
        return jsonResponse([], 422, false,"شما اجازه دسترسی ندارید .", []);
    }
    $credentials = [
        'login' => $request->mobile_number,
        'password' =>md5($request->password),
    ];
 
    if (! $token = auth('admin')->setTTL(60*60*24)->attempt($credentials)) {
        return jsonResponse([], 422, false,"شما اجازه دسترسی ندارید .", []);
    }
    $admin=auth('admin')->user()->toArray();
    return jsonResponse(array_merge($admin,[
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => 60*60*24,
    ]), 200, true, '', []);
}


   /**
 * @OA\Get(
 *     path="/profile",
 *     summary="Get admin details",
 *     tags={"Profile"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="avatar", type="string", example="http://example.com/avatar.jpg"),
 *                 @OA\Property(property="referral", type="string", example="CHNFOJ"),
 *                 @OA\Property(property="role", type="integer", example=0),
 *                 @OA\Property(property="login", type="string", example="user@example.com"),
 *                 @OA\Property(property="cellphone", type="string", example="123456789"),
 *                 @OA\Property(property="national_code", type="string", example="1234567890"),
 *                 @OA\Property(property="email", type="string", example="user@example.com"),
 *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2022-04-17 12:00:00"),
 *                 @OA\Property(property="firstname", type="string", example="John"),
 *                 @OA\Property(property="lastname", type="string", example="Doe"),
 *                 @OA\Property(property="phone_code", type="string", example="1234"),
 *                 @OA\Property(property="phone_code_send_time", type="string", format="date-time", example="2022-04-17 12:00:00"),
 *                 @OA\Property(property="wallet", type="float", example=100.00),
 *                 @OA\Property(property="wallet_expire", type="string", format="date-time", example="2022-04-17 12:00:00"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2022-04-17 12:00:00"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2022-04-17 12:00:00")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     )
 * )
 */

 public function AdminDetail()
 {
     $user=auth('admin')->user();
     $user=User::with('referrer')->with('referrals')->with('notifications')->find($user->id);
     if ($user->wallet_expire !== null) {
         $walletExpire = Carbon::parse($user->wallet_expire);
         $currentTime = Carbon::now();
         if ($currentTime->greaterThanOrEqualTo($walletExpire)) {
             $r_user=User::find($user->id);
             $r_user->wallet_expire=null;
             $r_user->save();
         } 
     }
     $wallet_gift=floatval($user['wallet_gift'])??0;
     $wallet=floatval($user['wallet'])??0;
     $user['wallet']=$wallet_gift+$wallet;
     unset($user['wallet_gift']);
     unset($user['wallet_expire']);
     unset($user['login']);
     unset($user['password']);
     unset($user['login_level']);
     unset($user['phone_code']);
     unset($user['phone_code_send_time']);
     $user->avatar=$user->avatar?url('storage/'.$user->avatar):''; 
     return jsonResponse($user, 200, true,'', []);
 }



/**
 * @OA\Post(
 *     path="/logout",
 *     summary="Log out the currently authenticated Admin",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Successfully logged out")
 *         )
 *     )
 * )
 */
public function AdminLogOut()
{
    Auth::guard('admin')->logout();

    return jsonResponse([], 200, true,'با موفقیت خارج شد.', []);
}


}