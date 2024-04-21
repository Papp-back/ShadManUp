<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller\api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','verify','loginapp']]);
    }

    public function loginapp(Request $request)
    {
        return response()->json([
            "message"=> "Unauthenticated."
          ], 401);
    }
    /**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="User login or registration and send verification code via SMS",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"mobile"},
 *             @OA\Property(property="mobile", type="string", format="phone number", example="1234567890")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="پیامک با موفقیت ارسال شد ."),
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
 *             @OA\Property(property="message", type="string", example="خطا در ارسال پیامک"),
 *             @OA\Property(property="errors", type="array", @OA\Items())
 *         )
 *     ),
 * )
 */
    public function login(Request $request)
    {
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        // Check if the user exists
        $user = User::where('cellphone', $request->input('mobile'))->first();
        $code=rand(1000,9999);
        $send_sms=false;
        if (!$user) {
            User::create([
                'cellphone'=>$request->input('mobile'),
                'login'=>$request->input('mobile'),
                'password'=>md5(rand(100000,999999999)),
                'phone_code'=>$code,
                'phone_code_send_time'=>Carbon::now(),
            ]);
            $user = User::where('cellphone', $request->input('mobile'))->first();
            $send_sms=true;
        }
        if ($user && ($user->phone_code_send_time < Carbon::now()->subMinutes(1)||$send_sms)) {
			// $mobile=$user->cellphone;
			// $curl = curl_init();

			// curl_setopt_array($curl, array(
			// CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
			// CURLOPT_RETURNTRANSFER => true,
			// CURLOPT_ENCODING => '',
			// CURLOPT_MAXREDIRS => 10,
			// CURLOPT_TIMEOUT => 0,
			// CURLOPT_FOLLOWLOCATION => true,
			// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			// CURLOPT_CUSTOMREQUEST => 'POST',
			// CURLOPT_POSTFIELDS =>'{
			// "mobile": "'.$mobile.'",
			// "templateId": 116063,
			// "parameters": [
			// 	{
			// 	"name": "code",
			// 	"value": "'.$code.'"
			// 	}

			// ]
			// }',
			// CURLOPT_HTTPHEADER => array(
			// 	'Content-Type: application/json',
			// 	'Accept: text/plain',
			// 	'x-api-key: 9ASwc4hnS90mQ0h5ulkkAFdLggDEy6j3QJGxl35310424qFkTPpVtQPssG9Thf6v'
			// ),
			// ));

			// $response = curl_exec($curl);

			// curl_close($curl);
			// $response=json_decode($response);
			$response=(object)['status'=>1];
			if($response->status==1){
				$user->phone_code=$code;
				$user->phone_code_send_time=Carbon::now();
				$user->save();
				return jsonResponse([], 200, true, 'پیامک با موفقیت ارسال شد .', []);
			}else{
                return jsonResponse([], 422, false,'خطا در ارسال پیامک', []);
					
			}
		}else{
            return jsonResponse([], 422, false,'شما در هر دقیقه 1 درخواست میتوانید ارسال کنید .', []);
        }

        
    }
    /**
 * @OA\Post(
 *     path="/auth/verify",
 *     summary="Verify user credentials and generate JWT token",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"mobile", "code"},
 *             @OA\Property(property="mobile", type="string", format="phone number", example="1234567890"),
 *             @OA\Property(property="code", type="string", example="1234")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *                 @OA\Property(property="refresh_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *                 @OA\Property(property="token_type", type="string", example="bearer"),
 *                 @OA\Property(property="expires_in", type="integer", example=43200),
 *                 @OA\Property(property="userDetail", type="object",
 *                     @OA\Property(property="id", type="integer", example=3),
 *                     @OA\Property(property="avatar", type="string", example="http://localhost:8000/storage"),
 *                     @OA\Property(property="refrral", type="string", example="CHNFOK"),
 *                     @OA\Property(property="role", type="integer", example=0),
 *                     @OA\Property(property="login", type="string", example="09376176535"),
 *                     @OA\Property(property="cellphone", type="string", example="09376176535"),
 *                     @OA\Property(property="national_code", type="string", nullable=true),
 *                     @OA\Property(property="email", type="string", nullable=true),
 *                     @OA\Property(property="password", type="string", example="e3260dc9187619156facbf082fd27f34"),
 *                     @OA\Property(property="email_verified_at", type="string", nullable=true),
 *                     @OA\Property(property="firstname", type="string", nullable=true),
 *                     @OA\Property(property="lastname", type="string", nullable=true),
 *                     @OA\Property(property="phone_code", type="string", example="3116"),
 *                     @OA\Property(property="phone_code_send_time", type="string", example="2024-04-17 03:27:46"),
 *                     @OA\Property(property="wallet", type="string", nullable=true),
 *                     @OA\Property(property="created_at", type="string", example="2024-04-16T23:54:19.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2024-04-16T23:57:46.000000Z")
 *                 )
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or invalid credentials",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="شماره موبایل نامعتبر است.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 * )
 */

    public function verify(Request $request) {
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        $user = User::where('cellphone', $request->input('mobile'))->first();
        if (!$user) {
            return jsonResponse([], 422, false,'شماره موبایل نامعتبر است.', []);
        }
        $credentials = [
            'login' => $request->mobile,
            'password' =>$user->password,
        ];
        if (! $token = auth('api')->setTTL(60*60*24)->attempt($credentials)) {
            return jsonResponse([], 422, false,'شماره موبایل نامعتبر است.', []);
        }

        if ($request->input('code')!=$user->phone_code) {
            return jsonResponse([], 422, false,'کد ارسالی نامعتبر است .', []);
        }
        return $this->respondWithToken($token);
    }
   /**
 * @OA\Post(
 *     path="/auth/user-referral",
 *     summary="Process user referral and update wallets",
 *     tags={"Authentication"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"referral_code"},
 *             @OA\Property(property="referral_code", type="string", example="CHNFOJ")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="کد معرف با موفقیت به ثبت رسید")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or invalid referral code",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="کد معرف کاربر قبلا به ثبت رسیده است")
 *         )
 *     )
 * )
 */

    public function userReferral(Request $request) {
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        $user=auth('api')->user();
        $user_main= User::find($user->id);
        if ($user->login_level!=3) {
            $user_referrer = User::where('referral', $request->input('referral_code'))->first();
            
            if (!$user_main) {
                return jsonResponse([], 422, false,'کاربر مورد نظر وجود ندارد .', []);
            }
            if (!$user || !$user_main) {
                return jsonResponse([], 422, false,'کد معرف نامعتبر است.', []);
            }
            if ($user_referrer) {
                $user_referrer->wallet=floatval($user_referrer->wallet)+10000;
                $user_referrer->save();
                $user_main->wallet_gift=100000;
                $user_main->wallet_expire=Carbon::now()->addDays(15);
                $user_main->referrer=$user_referrer->referral;
                $user_main->ref_level=$user_referrer->getReferralLevel()+1;
            }
            
            $user_main->login_level=3;
            $user_main->phone_code=null;
           
            $user_main->save();
            return jsonResponse([], 200, true,'کد معرف با موفقیت به ثبت رسید', []);
        }else{
            return jsonResponse([], 422, false,'کد معرف کاربر قبلا به ثبت رسیده است', []);
        }

        
    }
   /**
 * @OA\Get(
 *     path="/user/detail",
 *     summary="Get user details",
 *     tags={"User"},
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

    public function userDetail()
    {
        $user=auth('api')->user();
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
    protected function respondWithToken($token)
    {
        
		$user=auth('api')->user();
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
		$user->avatar=asset('storage/' . $user->avatar);
        $wallet_gift=floatval($user['wallet_gift'])??0;
        $wallet=floatval($user['wallet'])??0;
        $user['wallet']=$wallet_gift+$wallet;
        $user->avatar=$user->avatar?url('storage/'.$user->avatar):''; 
        unset($user['wallet_gift']);
        unset($user['wallet_expire']);
        return jsonResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 60*60*24,
            'userDetail' => $user,
        ], 200, true, '', []);
    }
    
}