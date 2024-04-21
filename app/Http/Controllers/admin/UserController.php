<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller\admin;
use App\Models\User;


class UserController extends Controller
{
     
    /**
 * @OA\Get(
 *     path="/users",
 *     summary="Get users with pagination",
 *     tags={"User"},
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

 public function getUsers(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    // Start building the query
    $query = User::query()->with('referrer')->with('referrals')->with('notifications');
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('firstname', 'like', '%' . $search . '%');
            $q->orWhere('lastname', 'like', '%' . $search . '%');
            $q->orWhere('cellphone', 'like', '%' . $search . '%');
            $q->orWhere('national_code', 'like', '%' . $search . '%');
            
        });
    }
    // Execute the query and paginate the results
    $users = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedUsers = $users->map(function ($user,$index) {
        $user->avatar=$user->avatar?url('storage/'.$user->avatar):'';    
        return $user->withJdateHuman();
    });

  
    return jRWithPagination($users, $transformedUsers, 200, true, '', []);
}

/**
* @OA\Get(
*     path="/users/{id}",
*     summary="Retrieve a single user by ID",
*     tags={"User"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the user",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
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
*             @OA\Property(property="message", type="string", example="کاربر وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items()),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function singleUser($id,Request $request) {
$user = User::with('referrer')->with('referrals')->with('notifications')->find($id);

if (!$user) {
    return jsonResponse([], 200, false, 'کاربر وجود ندارد .', []);
}
$user->avatar=$user->avatar?url('storage/'.$user->avatar):'';
return jsonResponse($user->withJdateHuman(), 200, true, '', []);
}
/**
 * @OA\Put(
 *     path="/users/{id}",
 *     summary="Update an existing user",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the user to update",
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
 *             type="object",
 *             @OA\Property(property="cellphone", type="string", description="User's cellphone number"),
 *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
 *             @OA\Property(property="firstname", type="string", description="User's first name"),
 *             @OA\Property(property="lastname", type="string", description="User's last name"),
 *             @OA\Property(property="national_code", type="string", description="User's national code"),
 *             @OA\Property(property="role", type="integer", description="User's role (admin will be 1 or user will be 0)")
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


public function updateUser($id,Request $request)
{
    $validator = ValidationFeilds($request, __FUNCTION__);
    if ($validator) {
        return $validator;
    }

    $user = User::find($id);
    if (!$user) {
        return jsonResponse([], 404, false, 'کاربر پیدا نشد.', []);
    }


    $user->login=$request->input('cellphone');
    // Update course details with other fields
    $user->update($request->all());

    return jsonResponse($user, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}

/**
* @OA\Delete(
*     path="/users/{id}",
*     summary="Delete a user by ID",
*     tags={"User"},
*     @OA\Parameter(
*         name="id",
*         in="path",
*         description="ID of the user",
*         required=true,
*         @OA\Schema(type="integer", format="int64")
*     ),
*     @OA\Response(
*         response=200,
*         description="user deleted successfully",
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
*         description="user not found",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="data", type="object"),
*             @OA\Property(property="status", type="integer", example=404),
*             @OA\Property(property="success", type="boolean", example=false),
*             @OA\Property(property="message", type="string", example="کاربر وجود ندارد ."),
*             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
*         ),
*     ),
*     security={{"bearerAuth": {}}},
* )
*/

public function destroyUser($id,Request $request) {
    $user = User::find($id);
    if (!$user) {
        return jsonResponse([], 404, false, 'کاربر وجود ندارد .', []);
    }
    
    $user->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}
}