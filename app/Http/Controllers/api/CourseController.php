<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseComment;
use App\Models\CommentLike;



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
    $query = Course::query()->with('category')->with('sections')->with('comments');
    if ($category_id) {
        $query->where('category_id', $category_id);
    }
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            
        });
    }
    $user_id=auth('api')->user()->id;
    // Execute the query and paginate the results
    $courses = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedCourses = $courses->map(function ($course,$index) use ($user_id){
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
            $course->sessions_count = "0";
            $course->total_duration_time = '0';
        }
        $course->comments = $course->comments->map(function ($comment) use ($user_id) {
            // Check if the comment has likes
            if ($comment->likes) {
                $comment->likes->each(function ($like) use ($comment, $user_id) {
                    // Check if the user has liked this comment
                    $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                     return $like; 
                });
            }
            return $comment->withJdateHuman();
        });
        $course->comments_count = $course->comments()->count()??0;
    
        
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
*         description="ID of the course",
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
*             @OA\Property(property="message", type="string", example="دروه وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function singleCourse($id,Request $request) {
    $course = Course::with('category')->with('sections')->find($id);
    
    if (!$course) {
        return jsonResponse([], 404, false, 'دروه وجود ندارد .', []);
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
    
    $user_id=auth('api')->user()->id;
    $course->comments = $course->comments->map(function ($comment) use ($user_id) {
        // Check if the comment has likes
        if ($comment->likes) {
            $comment->likes->each(function ($like) use ($comment, $user_id) {
                // Check if the user has liked this comment
                $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                 return $like; 
            });
        }
        return $comment->withJdateHuman();
    });
    $course->comments_count = $course->comments()->count()??0;
    $course->image=url('storage/'.$course->image);
    return jsonResponse($course->withJdateHuman(), 200, true, '', []);
}



/**
* @OA\Get(
*     path="/courses/{id}/comments",
*     summary="Retrieve comments of course by ID",
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
*         description="Comments of Course retrieved successfully",
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
*         description="Comments of Course not found",
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


public function getCommentsCourse($id,Request $request) {

    $user_id=auth('api')->user()->id;
    $comments = CourseComment::with('likes')->with('user')->withCount('likes')->where('course_id',$id)->get();
    $comments= $comments->map(function ($comment) use ($user_id) {
        // Check if the comment has likes
        if ($comment->likes) {
            $comment->likes->each(function ($like) use ($comment, $user_id) {
                // Check if the user has liked this comment
                $comment->user_like = ($like->id == $user_id) ? 1 : 0;
                 return $like; 
            });
        }
        return $comment->withJdateHuman();
    });
    
    return jsonResponse($comments, 200, true, '', []);
}


/**
 * @OA\Post(
 *      path="/courses/{id}/comments",
 *     summary="Store a new comment for a course",
 *     tags={"Course"},
 *       @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the course",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"comment"},
 *             @OA\Property(property="comment", type="integer", example="Lorem ipsum dolor sit amet, consectetur adip")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *              @OA\Property(property="data", type="object", ref="#/components/schemas/CommentCourse"),
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
 *     @OA\Response(
 *         response=400,
 *         description="ValidationError",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items()),
 *             @OA\Property(property="status", type="integer", example=400),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="شما یک نظر برای این دوره ثبت کرده اید."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */
public function setCommentsCourse($id,Request $request) {
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user_id=auth('api')->user()->id;
    $comment_user = CourseComment::where('course_id',$id)->where('user_id',$user_id)->first();
    if ($comment_user) {
        return jsonResponse([], 400, false, 'شما یک نظر برای این دوره ثبت کرده اید.', []);
    }
    $commentData=$request->all();
    $commentData['user_id']=$user_id;
    $commentData['course_id']=$id;
    $comment=CourseComment::create($commentData);
    
    return jsonResponse($comment, 200, true, '', []);
}
/**
 * @OA\Post(
 *      path="/courses/commentlike",
 *     summary="Store a new comment like for a course",
 *     tags={"Course"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"comment_id"},
 *             @OA\Property(property="comment_id", type="integer", example=1),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Comment liked successfully",
 *         @OA\JsonContent(
 *             type="object",
 *              @OA\Property(property="data", type="object", ref="#/components/schemas/CommentCourse"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت ثبت شد ."),
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
public function setCommentLikeCourse(Request $request) {
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user_id=auth('api')->user()->id;
    $comment_id=$request->input('comment_id');
    $comment_user = CommentLike::where('course_comment_id',$comment_id)->where('user_id',$user_id)->first();
    if (!$comment_user) {
        $commentData=$request->all();
        $commentData['course_comment_id']=$comment_id;
        $commentData['user_id']=$user_id;
        $comment=CommentLike::create($commentData);
    }
    
    
    return jsonResponse([], 200, true, 'با موفقیت ثبت شد.', []);
}





}