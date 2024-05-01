<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\CourseComment;
class CourseCommentController
{
    
 /**
 * @OA\Get(
 *     path="/commentcourses",
 *     summary="Get comments of courses with pagination",
 *     tags={"CommentCourse"},
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
 *         description="user Id query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="course_id",
 *         in="query",
 *         description="course Id query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="show",
 *         in="query",
 *         description="show query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommentCourse")),
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

 public function getCommentCourses(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $user_id = $request->input('user_id');
    $course_id = $request->input('course_id');
    $show = $request->input('show');
    // Start building the query
    $query = CourseComment::query()->with('user')->with('course')->with('likes');
    if ($user_id) {
        $query->where('user_id', $user_id);
    }
    if ($course_id) {
        $query->where('course_id', $course_id);
    }
    if ($show) {
        $query->where('show', $show);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('comment', 'like', '%' . $search . '%');
            
        });
    }
    $query->orderBy('id', 'desc');
    // Execute the query and paginate the results
    $courses = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedCourses = $courses->map(function ($course) {
        return $course->withJdateHuman();
    });
    return jRWithPagination($courses, $transformedCourses, 200, true, '', []);
}


/**
* @OA\Get(
 *     path="/commentcourses/{id}",
 *     summary="Retrieve a single comment of course by ID",
 *     tags={"CommentCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the comment",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/CommentCourse"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example=""),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="دروه وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function singleCommentCourse($id,Request $request) {
    $course = CourseComment::with('user')->with('course')->with('likes')->find($id);
    if (!$course) {
        return jsonResponse([], 200, false, 'دروه وجود ندارد .', []);
    }
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
}
/**
* @OA\Delete(
*     path="/commentcourses/{id}",
*     summary="Delete a comment of course by ID",
*     tags={"CommentCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the Comment",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Comment of course deleted successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Comment of course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=404),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function destroyCommentCourse($id,Request $request) {
    $CourseComment = CourseComment::find($id);
    if (!$CourseComment) {
        return jsonResponse($CourseComment, 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $CourseComment->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}
/**
* @OA\Patch(
*     path="/commentcourses/{id}/show",
*     summary="show comment of course",
*     tags={"CommentCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the Comment",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Comment of course showed successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Comment of course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=404),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/
public function ShowCommentCourse($id,Request $request) {
    $CourseComment = CourseComment::find($id);
    if (!$CourseComment) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    $CourseComment->show=1;
    $CourseComment->save();
    return jsonResponse([], 200, true, 'با موفقیت اعمال شد.', []);
}

/**
* @OA\Patch(
*     path="/commentcourses/{id}/hide",
*     summary="show comment of course",
*     tags={"CommentCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the Comment",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Comment of course hided successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example="با موفقیت حذف شد."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     @OA\Response(
*         response=404,
*         description="Comment of course not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=404),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="آیتم وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/
public function HideCommentCourse($id,Request $request) {
    $CourseComment = CourseComment::find($id);
    if (!$CourseComment) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    $CourseComment->show=0;
    $CourseComment->save();
    return jsonResponse([], 200, true, 'با موفقیت اعمال شد.', []);
}


}