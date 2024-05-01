<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller\admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CourseSession;
use App\Models\CourseSection;

class CourseSessionController extends Controller
{
    
 /**
 * @OA\Get(
 *     path="/sessioncourses",
 *     summary="Get sessions of courses with pagination",
 *     tags={"SessionCourse"},
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
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SessionCourse")),
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

 public function getSessionCourses(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    // Start building the query
    $query = CourseSession::query()->with('courseSection');
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            $q->orWhere('description', 'like', '%' . $search . '%');
            
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
 * @OA\Post(
 *     path="/sessioncourses",
 *     summary="Store a new session course",
 *     tags={"SessionCourse"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"course_section_id", "title", "description", "duration_minutes"},
 *             @OA\Property(property="course_section_id", type="integer", format="int64", example=1, description="The ID of the section of course"),
 *             @OA\Property(property="title", type="string", example="Session Title", description="The title of the session"),
 *             @OA\Property(property="description", type="string", example="Session Description", description="The description of the session"),
 *             @OA\Property(property="file_url", type="string", example="http://example.com", description="The file url of the session(externall link like youtube or aparat video link)"),
 * @OA\Property(property="duration_minutes", type="integer", format="int64", example=5, description="The duration of the associated course"),

 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Session course created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/SessionCourse"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Session course created successfully"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="object", ref="#/components/schemas/ValidationError"),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

public function StoreSessionCourse(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }

    // Create the course
    $course = CourseSession::create($request->all());

    return jsonResponse($course, 200, true,  'با موفقیت ایجاد شد .', []);
}

/**
* @OA\Get(
 *     path="/sessioncourses/{id}",
 *     summary="Retrieve a single session of course by ID",
 *     tags={"SessionCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the session",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/SessionCourse"),
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

public function singleSessionCourse($id,Request $request) {
    $course = CourseSession::find($id);
    if (!$course) {
        return jsonResponse([], 200, false, 'دروه وجود ندارد .', []);
    }
    if ($course->file_path) {
        $course->file_path=url('storage/'.$course->file_path);
    }
    $course->section=CourseSection::with('course')->find($course->course_section_id);
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
}
  /**
 * @OA\Post(
 *     path="/sessioncourses/{id}/file",
 *     summary="Update or Insert an existing SessionCourse file",
 *     tags={"SessionCourse"},
 *     security={{ "bearerAuth":{} }},
 *      @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the session",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"file"},
 *                 @OA\Property(property="file", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="file", type="string", example="http://example.com/storage/sessions")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or image not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="فایلی ارسال نشده است")
 *         )
 *     )
 * )
 */

 public function updateCourseFile($id,Request $request)
 {
     $validator = ValidationFeilds($request, __FUNCTION__);
     if ($validator) {
         return $validator;
     }
 
     $course = CourseSession::find($id);
     if (!$course) {
         return jsonResponse([], 404, false, 'آیتم پیدا نشد.', []);
     }
     // Store the avatar
     if ($course->file_path) {
        Storage::disk('public')->delete($course->file_path);
    }
    // Store the new file
    $file = $request->file('file');
    $filePath = $file->store('sessions', 'public');
    $course->file_path = $filePath;
    $course->file_name = $file->getClientOriginalName();
    $course->file_type = $file->getClientMimeType();
    $course->file_size = $file->getSize();
    $course->save();
    return jsonResponse(['url' => url('storage/' . $filePath)], 200, true, 'با موفقیت بروزرسانی شد.', []);
 }
/**
 * @OA\Put(
 *     path="/sessioncourses/{id}",
 *     summary="Update an existing session course",
 *     tags={"SessionCourse"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the session course to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64",
 *             example=1
 *         )
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"course_section_id", "title", "description", "duration_minutes"},
 *             @OA\Property(property="course_section_id", type="integer", format="int64", example=1, description="The ID of the section of course"),
 *             @OA\Property(property="title", type="string", example="Session Title", description="The title of the session"),
 *             @OA\Property(property="file_url", type="string", example="Session file url", description="The file url of the session"),
 *             @OA\Property(property="description", type="string", example="Session Description", description="The description of the session"),
 * @OA\Property(property="duration_minutes", type="integer", format="int64", example=5, description="The duration of the associated course"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Session course updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/SessionCourse"),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Session course not found",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError"),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation errors",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError"),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */


public function updateSessionCourse($id,Request $request)
{
    $validator = ValidationFeilds($request, 'StoreSessionCourse');
    if ($validator) {
        return $validator;
    }
    $course = CourseSession::find($id);
    if (!$course) {
        return jsonResponse([], 404, false, 'دروه پیدا نشد.', []);
    }
    $course->update($request->all());
    return jsonResponse($course, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}
/**
* @OA\Delete(
*     path="/sessioncourses/{id}",
*     summary="Delete a session of course by ID",
*     tags={"SessionCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the Session",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Session of course deleted successfully",
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
*         description="Session of course not found",
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

public function destroySessionCourse($id,Request $request) {
    $CourseSession = CourseSession::find($id);
    if (!$CourseSession) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $CourseSession->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}

}