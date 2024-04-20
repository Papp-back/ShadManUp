<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\User;
class AuthController
{
        /**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Admin login and get Admin Data",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"mobile","password"},
 *             @OA\Property(property="mobile", type="string", format="phone number", example="1234567890"),
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
    $admin = User::where('cellphone', $request->input('mobile'))->where('role', 1)->first();
   
    if (!$admin) {
        return jsonResponse([], 422, false,"شما اجازه دسترسی ندارید .", []);
    }
    $credentials = [
        'login' => $request->mobile,
        'password' =>md5($request->password),
    ];
 
    if (! $token = auth('admin')->setTTL(60*60*24)->attempt($credentials)) {
        return jsonResponse([], 422, false,"شما اجازه دسترسی ندارید .", []);
    }
    $admin=auth('admin')->user();
    return jsonResponse([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => 60*60*24,
        'adminDetail' => $admin,
    ], 200, true, '', []);
   

    
}
}