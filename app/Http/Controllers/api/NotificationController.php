<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Notification;
class NotificationController
{
    /**
 * @OA\Get(
 *     path="/notifications",
 *     summary="Get user notifications with pagination",
 *     tags={"Notification"},
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
 *         name="read",
 *         in="query",
 *         description="Filter notifications by read status (0 for unread, 1 for read)",
 *         required=false,
 *         @OA\Schema(type="integer", default=0)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Notification")),
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


 public function getNotifications(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $read = $request->input('read');
    $user_id=auth('api')->user()->id;
    // Start building the query
    $query = Notification::query()->with('user');
    $query->where('user_id', $user_id);
    // Execute the query and paginate the results
    $notifications = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedNotification = $notifications->map(function ($notification) {
        $nofif=Notification::find($notification->id);
        $nofif->read=1;
        $notif->save();
        return $notification->withJdateHuman();
    });
    return jRWithPagination($notifications, $transformedNotification, 200, true, '', []);
}


/**
 * @OA\Get(
 *     path="/notifications/{id}",
 *     summary="Get a single user notification by ID",
 *     tags={"Notification"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the notification to retrieve",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/Notification"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Notification not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */


 public function singleNotification($id,Request $request) {
    $Notification = Notification::with('user')->find($id);
    if (!$Notification) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    $Notification->read=1;
    return jsonResponse($Notification->withJdateHuman(), 200, true, '', []);
}

}
