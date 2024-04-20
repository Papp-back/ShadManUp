<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller\admin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Course;
class CourseController extends Controller
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
* @OA\Post(
*     path="/courses",
*     summary="Store a new Course",
*     tags={"Course"},
 *         @OA\RequestBody(
 *             required=true,
 *             @OA\MediaType(
 *                 mediaType="multipart/form-data",
 *                 @OA\Schema(
 *                     required={"title", "category_id", "author", "description", "price", "image"},
 *                     @OA\Property(property="title", type="string", example="Course Title"),
 *                     @OA\Property(property="category_id", type="integer", format="int64", example=1),
 *                     @OA\Property(property="author", type="string", example="John Doe"),
 *                     @OA\Property(property="description", type="string", example="Course description"),
 *                     @OA\Property(property="price", type="number", format="float", example=50.99),
 *                     @OA\Property(property="discount", type="number", format="float", example=10.0),
 *                     @OA\Property(property="session", type="integer", format="int32", example=20),
 *                     @OA\Property(property="summary", type="string", example="Course summary"),
 *                     @OA\Property(property="image", type="string", format="binary"),
 *                 ),
 *             ),
 *         ),
*     @OA\Response(
*         response=200,
*         description="Course created successfully",
*         @OA\JsonContent(
*             type="object",
*              @OA\Property(property="data", type="object", ref="#/components/schemas/Course"),
*             @OA\Property(property="status", type="integer", example=200),
*             @OA\Property(property="success", type="boolean", example=true),
*             @OA\Property(property="message", type="string", example="با موفقیت ایجاد شد ."),
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

public function StoreCourse(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    // Handle image upload
    $imagePath = $request->file('image')->store('courses', 'public');

    // Create the course
    $course = Course::create(array_merge($request->all(), ['image' => $imagePath]));

    return jsonResponse($course, 200, true,  'با موفقیت ایجاد شد .', []);
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
/**
 * @OA\Put(
 *     path="/courses/{id}",
 *     summary="Update an existing course",
 *     tags={"Course"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the course to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64",
 *             example=1
 *         )
 *     ),
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *              required={"title", "category_id", "author", "description", "price"},
 *             @OA\Property(property="title", type="string", example="Updated Course Title"),
 *             @OA\Property(property="category_id", type="integer", format="int64", example=2),
 *             @OA\Property(property="author", type="string", example="Jane Doe"),
 *             @OA\Property(property="description", type="string", example="Updated course description"),
 *             @OA\Property(property="price", type="number", format="float", example=69.99),
 *             @OA\Property(property="discount", type="number", format="float", example=20.0),
 *             @OA\Property(property="summary", type="string", example="Updated course summary"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Course updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Course"),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Course not found",
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


public function updateCourse($id,Request $request)
{
    $validator = ValidationFeilds($request, __FUNCTION__);
    if ($validator) {
        return $validator;
    }

    $course = Course::find($id);
    if (!$course) {
        return jsonResponse([], 404, false, 'درس پیدا نشد.', []);
    }



    // Update course details with other fields
    $course->update($request->all());

    return jsonResponse($course, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}
  /**
 * @OA\Post(
 *     path="/courses/{id}/image",
 *     summary="Update an existing course image",
 *     tags={"Course"},
 *     security={{ "bearerAuth":{} }},
 * *     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the course",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"image"},
 *                 @OA\Property(property="image", type="string", format="binary")
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="avatar", type="string", example="http://example.com/storage/course/course_1.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or image not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="تصویری ارسال نشده است")
 *         )
 *     )
 * )
 */

 public function updateCourseImage($id,Request $request)
 {
     $validator = ValidationFeilds($request, __FUNCTION__);
     if ($validator) {
         return $validator;
     }
 
     $course = Course::find($id);
     if (!$course) {
         return jsonResponse([], 404, false, 'درس پیدا نشد.', []);
     }
 
     // Store the avatar
     if ($course->image) {
        Storage::delete($course->image);
    }

    // Store the new image
    $imagePath = $request->file('image')->store('courses', 'public');
    $course->image = $imagePath;
    $course->save();
    return jsonResponse(['url' => url('storage/' . $imagePath)], 200, true, 'با موفقیت بروزرسانی شد.', []);
 }
/**
* @OA\Delete(
*     path="/courses/{id}",
*     summary="Delete a course by ID",
*     tags={"Course"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the course",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="course deleted successfully",
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
*         description="course not found",
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

public function destroyCourse($id,Request $request) {
    $course = Category::find($id);
    if (!$course) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $course->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}

}