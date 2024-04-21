<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller\admin;
use Illuminate\Http\Request;
use App\Models\CourseSection;
class CourseSectionController extends Controller
{
    
 /**
 * @OA\Get(
 *     path="/sectioncourses",
 *     summary="Get sections of courses with pagination",
 *     tags={"SectionCourse"},
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
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SectionCourse")),
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

 public function getSectionCourses(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    // Start building the query
    $query = CourseSection::query()->with('course')->withCount('sessions');
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            $q->orWhere('description', 'like', '%' . $search . '%');
            
        });
    }
    // Execute the query and paginate the results
    $courses = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedCourses = $courses->map(function ($course) {
        return $course->withJdateHuman();
    });
    return jRWithPagination($courses, $transformedCourses, 200, true, '', []);
}
/**
 * @OA\Post(
 *     path="/sectioncourses",
 *     summary="Store a new section course",
 *     tags={"SectionCourse"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"course_id", "title", "description"},
 *             @OA\Property(property="course_id", type="integer", format="int64", example=1, description="The ID of course"),
 *             @OA\Property(property="title", type="string", example="Section Title", description="The title of the section"),
 *             @OA\Property(property="description", type="string", example="Section Description", description="The description of the section"),
 *                     @OA\Property(property="price", type="number", format="integer", example=50000),
 *                     @OA\Property(property="discount", type="number", format="integer", example=1000),

 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Section course created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/SectionCourse"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Section course created successfully"),
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

public function StoreSectionCourse(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }

    // Create the course
    $course = CourseSection::create($request->all());

    return jsonResponse($course, 200, true,  'با موفقیت ایجاد شد .', []);
}

/**
* @OA\Get(
 *     path="/sectioncourses/{id}",
 *     summary="Retrieve a single section of course by ID",
 *     tags={"SectionCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the section",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/SectionCourse"),
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

public function singleSectionCourse($id,Request $request) {
    $course = CourseSection::with('course')->withCount('sessions')->find($id);
    if (!$course) {
        return jsonResponse([], 200, false, 'دروه وجود ندارد .', []);
    }
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
}
/**
 * @OA\Put(
 *     path="/sectioncourses/{id}",
 *     summary="Update an existing section course",
 *     tags={"SectionCourse"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the section course to update",
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
 *             required={"course_id", "title", "description"},
 *             @OA\Property(property="course_id", type="integer", format="int64", example=1, description="The ID of course"),
 *             @OA\Property(property="title", type="string", example="Section Title", description="The title of the section"),
 *             @OA\Property(property="description", type="string", example="Section Description", description="The description of the section"),
 *                     @OA\Property(property="price", type="number", format="integer", example=50000),
 *                     @OA\Property(property="discount", type="number", format="integer", example=1000),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Section course updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/SectionCourse"),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Section course not found",
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


public function updateSectionCourse($id,Request $request)
{
    $validator = ValidationFeilds($request, 'StoreSectionCourse');
    if ($validator) {
        return $validator;
    }
    $course = CourseSection::find($id);
    if (!$course) {
        return jsonResponse([], 404, false, 'دروه پیدا نشد.', []);
    }
    $course->update($request->all());
    return jsonResponse($course, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}
/**
* @OA\Delete(
*     path="/sectioncourses/{id}",
*     summary="Delete a section of course by ID",
*     tags={"SectionCourse"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the Section",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Section of course deleted successfully",
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
*         description="Section of course not found",
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

public function destroySectionCourse($id,Request $request) {
    $CourseSection = CourseSection::find($id);
    if (!$CourseSection) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $CourseSection->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}

}