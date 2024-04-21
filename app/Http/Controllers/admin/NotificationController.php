<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\Notification;
class NotificationController
{
 /**
 * @OA\Get(
 *     path="/notifications",
 *     summary="Get notifications with pagination",
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
 *         name="user_id",
 *         in="query",
 *         description="Filter notifications by user ID",
 *         required=false,
 *         @OA\Schema(type="integer")
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
    $user_id = $request->input('user_id');
    $read = $request->input('read');
    // Start building the query
    $query = Notification::query()->with('user');
    if ($user_id) {
        $query->where('user_id', $user_id);
    }
    if ($read) {
        $query->where('read', $read);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            $q->orWhere('content', 'like', '%' . $search . '%');
            
        });
    }
    // Execute the query and paginate the results
    $notifications = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedNotification = $notifications->map(function ($notification) {
        return $notification->withJdateHuman();
    });
    return jRWithPagination($notifications, $transformedNotification, 200, true, '', []);
}
/**
 * @OA\Post(
 *     path="/notifications",
 *     summary="Store a new notification",
 *     tags={"Notification"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Notification data",
 *         @OA\JsonContent(
 *             @OA\Property(property="user_id", type="integer",example="1", description="The ID of the user to whom the notification belongs"),
 *             @OA\Property(property="title", type="string", example="Lorem ipsum", description="The title of the notification"),
 *             @OA\Property(property="content", type="string",example="Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia,molestiae quas vel sint commodi repudiandae consequuntur voluptatum laborum numquam blanditiis harum quisquam eius sed odit fugiat iusto fuga praesentium optio, eaque rerum!", description="The content of the notification"),
 *             @OA\Property(property="read", type="boolean", description="Indicates whether the notification has been read or not", example=false),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/Notification"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت ایجاد شد."),
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

public function StoreNotification(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    // Create the Notification
    $course = Notification::create($request->all());

    return jsonResponse($course, 200, true,  'با موفقیت ایجاد شد .', []);
}
/**
 * @OA\Get(
 *     path="/notifications/{id}",
 *     summary="Get a single notification by ID",
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
    return jsonResponse($Notification->withJdateHuman(), 200, true, '', []);
}

/**
 * @OA\Put(
 *     path="/notifications/{id}",
 *     summary="Update a notification by ID",
 *     tags={"Notification"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the notification to update",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Notification data",
 *         @OA\JsonContent(
 *             @OA\Property(property="user_id", type="integer", description="The ID of the user to whom the notification belongs"),
 *             @OA\Property(property="title", type="string", description="The title of the notification"),
 *             @OA\Property(property="content", type="string", description="The content of the notification"),
 *             @OA\Property(property="read", type="boolean", description="Indicates whether the notification has been read or not", example=false),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/Notification"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت به‌روزرسانی شد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Notification not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="آیتم پیدا نشد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */
public function updateNotification($id,Request $request)
{
    $validator = ValidationFeilds($request, 'StoreNotification');
    if ($validator) {
        return $validator;
    }

    $Notification = Notification::find($id);
    if (!$Notification) {
        return jsonResponse([], 404, false, 'آیتم پیدا نشد.', []);
    }



    // Update course details with other fields
    $Notification->update($request->all());

    return jsonResponse($Notification, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}


/**
 * @OA\Delete(
 *     path="/notifications/{id}",
 *     summary="Delete a notification by ID",
 *     tags={"Notification"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the notification to delete",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Notification not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="آیتم وجود ندارد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

public function destroyNotification($id,Request $request) {
    $Notification = Notification::find($id);
    if (!$Notification) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $Notification->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}
// /**
// * @OA\Patch(
// *     path="/commentcourses/{id}/show",
// *     summary="show comment of course",
// *     tags={"CommentCourse"},
// *     @OA\Parameter(
// *         name="id",
// *         in="path",
// *         description="ID of the Comment",
// *         required=true,
// *         @OA\Schema(type="integer", format="int64")
// *     ),
// *     @OA\Response(
// *         response=200,
// *         description="Comment of course showed successfully",
// *         @OA\JsonContent(
// *             type="object",
// *             @OA\Property(property="data", type="object"),
// *             @OA\Property(property="status", type="integer", example=200),
// *             @OA\Property(property="success", type="boolean", example=true),
// *             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
// *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
// *         ),
// *     ),
// *     @OA\Response(
// *         response=404,
// *         description="Comment of course not found",
// *         @OA\JsonContent(
// *             type="object",
// *             @OA\Property(property="data", type="object"),
// *             @OA\Property(property="status", type="integer", example=404),
// *             @OA\Property(property="success", type="boolean", example=false),
// *             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
// *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
// *         ),
// *     ),
// *     security={{"bearerAuth": {}}},
// * )
// */
// public function ShowCommentCourse($id,Request $request) {
//     $CourseComment = CourseComment::find($id);
//     if (!$CourseComment) {
//         return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
//     }
//     $CourseComment->show=1;
//     $CourseComment->save();
//     return jsonResponse([], 200, true, 'با موفقیت اعمال شد.', []);
// }

// /**
// * @OA\Patch(
// *     path="/commentcourses/{id}/hide",
// *     summary="show comment of course",
// *     tags={"CommentCourse"},
// *     @OA\Parameter(
// *         name="id",
// *         in="path",
// *         description="ID of the Comment",
// *         required=true,
// *         @OA\Schema(type="integer", format="int64")
// *     ),
// *     @OA\Response(
// *         response=200,
// *         description="Comment of course hided successfully",
// *         @OA\JsonContent(
// *             type="object",
// *             @OA\Property(property="data", type="object"),
// *             @OA\Property(property="status", type="integer", example=200),
// *             @OA\Property(property="success", type="boolean", example=true),
// *             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
// *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
// *         ),
// *     ),
// *     @OA\Response(
// *         response=404,
// *         description="Comment of course not found",
// *         @OA\JsonContent(
// *             type="object",
// *             @OA\Property(property="data", type="object"),
// *             @OA\Property(property="status", type="integer", example=404),
// *             @OA\Property(property="success", type="boolean", example=false),
// *             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
// *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
// *         ),
// *     ),
// *     security={{"bearerAuth": {}}},
// * )
// */
// public function HideCommentCourse($id,Request $request) {
//     $CourseComment = CourseComment::find($id);
//     if (!$CourseComment) {
//         return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
//     }
//     $CourseComment->show=0;
//     $CourseComment->save();
//     return jsonResponse([], 200, true, 'با موفقیت اعمال شد.', []);
// }

}