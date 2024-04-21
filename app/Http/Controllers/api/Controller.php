<?php

namespace App\Http\Controllers\api;

   /**
 * @OA\Info(
 *    title="APIs For application",
 *    version="1.0.0",
 *    description="API endpoints for the application.",
 *    @OA\Contact(
 *         email="viracodingGplus@gmail.com",
 *         name="viracoding",
 *      
 *     )
 * ),
* @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="development Server"
 * ),
 *  @OA\Server(
 *     url="https://shadmanup.ir/api/v1",
 *     description="Production Server"
 * ),
 * @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 * 
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model schema",
 *     @OA\Property(property="avatar", type="string"),
 *     @OA\Property(property="referral", type="string"),
 *     @OA\Property(property="cellphone", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time"),
 *     @OA\Property(property="firstname", type="string"),
 *     @OA\Property(property="national_code", type="string"),
 *     @OA\Property(property="lastname", type="string"),
 *     @OA\Property(property="wallet", type="number", format="float"),
 *     @OA\Property(property="wallet_expire", type="string", format="date-time"),
 *     @OA\Property(property="wallet_gift", type="number", format="float"),
 *     @OA\Property(property="password", type="string"),
 *     @OA\Property(property="phone_code", type="string"),
 *     @OA\Property(property="phone_code_send_time", type="string", format="date-time"),
 *     @OA\Property(property="role", type="integer"),
 *     @OA\Property(property="referrer", type="string"),
 *     @OA\Property(property="ref_level", type="integer"),
 *     @OA\Property(property="login_level", type="integer"),
 *     @OA\Property(property="login", type="string"),
 * )
 */

abstract class Controller
{
 
}