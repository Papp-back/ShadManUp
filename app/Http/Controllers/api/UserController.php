<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
class UserController
{
    /**
* @OA\Get(
*     path="/user/notifications",
*     summary="Retrieve all notifications of user",
*     tags={"User"},
*     @OA\Response(
*         response=200,
*         description="User retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/User"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example=""),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="user not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="آیتمی وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function userNotifications(Request $request) {
    $id=auth('api')->user()->id;
    $userNotification = Notification::where('user_id',$id)->get();
    
    if (!$userNotification) {
        return jsonResponse([], 200, false, 'آیتمی وجود ندارد .', []);
    }
    $userNotification=$userNotification->map(function ($notif) {
        return $notif->withJdateHuman();
    });
    return jsonResponse($userNotification, 200, true, '', []);
    }
}
