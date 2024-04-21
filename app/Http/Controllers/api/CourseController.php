<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Course;
class CourseController
{
        /**
 * @OA\Get(
 *     path="/courses",
 *     summary="Get courses with pagination",
 *     tags={"Course"},
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
 *         name="category_id",
 *         in="query",
 *         description="category_id query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Course")),
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

 public function getCourses(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $category_id = $request->input('category_id');
    // Start building the query
    $query = Course::query()->with('category')->with('sections');
    if ($category_id) {
        $query->where('category_id', $category_id);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            
        });
    }
    // Execute the query and paginate the results
    $courses = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedCourses = $courses->map(function ($course,$index) {
        $course->image=url('storage/'.$course->image);
        if (isset($course->sections[0])) {
            $course->sessions_count = $course->sections[0]->sessions()->count(); // Count sessions
            $totalDurationMinutes = $course->sections[0]->sessions()->sum('duration_minutes'); // Sum duration_minutes
            $course->total_duration_time = convertToTime($totalDurationMinutes); // Convert to human-readable time
            if ($totalDurationMinutes) {
               
                $course->sections[$index]->sessions->map(function ($session) {
                    // Convert duration_minutes to HH:MM:SS format
                    $session->duration_minutes = convertToTime($session->duration_minutes);
                    $session->file_size = formatFileSize($session->file_size);
                    return $session;
                });
            }
            $course->sections->each(function ($section) {
                return $section->prettifyPrice();
            });
        } else {
            $course->sessions_count = 0;
            $course->total_duration_time = '0';
        }
       
    
        
        return $course->prettifyPrice()->withJdateHuman();
    });

  
    return jRWithPagination($courses, $transformedCourses, 200, true, '', []);
}

/**
* @OA\Get(
*     path="/courses/{id}",
*     summary="Retrieve a single course by ID",
*     tags={"Course"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the category",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="Course retrieved successfully",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", ref="#/components/schemas/Course"),
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
*             @OA\Property(property="message", type="string", example="درس وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function singleCourse($id,Request $request) {
    $course = Course::with('category')->with('sections')->find($id);
    
    if (!$course) {
        return jsonResponse([], 200, false, 'درس وجود ندارد .', []);
    }
    if (isset($course->sections[0])) {
        $course->sessions_count = $course->sections[0]->sessions()->count(); // Count sessions
        $totalDurationMinutes = $course->sections[0]->sessions()->sum('duration_minutes'); // Sum duration_minutes
        $course->total_duration_time = convertToTime($totalDurationMinutes); // Convert to human-readable time
        if ($totalDurationMinutes) {
            $course->sections->map(function ($sec){
                $sec->sessions->map(function ($session) {
                    $session->duration_minutes = convertToTime($session->duration_minutes);
                    $session->file_size = formatFileSize($session->file_size);
                    return $session;
                });
            });
        }
        $course->sections->each(function ($section) {
            return $section->prettifyPrice();
        });
    } else {
        $course->sessions_count = 0;
        $course->total_duration_time = '0';
    }
    $course->image=url('storage/'.$course->image);
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
    }




}
