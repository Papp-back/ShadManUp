<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller\api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller
{
    /**
 * @OA\Post(
 *     path="/profile/save-avatar",
 *     summary="Save user's avatar",
 *     tags={"Profile"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"avatar"},
 *                 @OA\Property(property="avatar", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="avatar", type="string", example="http://example.com/storage/avatars/avatar_1.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or avatar not provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="تصویری ارسال نشده است")
 *         )
 *     )
 * )
 */

 public function saveAvatar(Request $request)
 {
     $validator = ValidationFeilds($request, __FUNCTION__);
     if ($validator) {
         return $validator;
     }
 
     // Store the avatar
     if ($request->hasFile('avatar')) {
         $avatar = $request->file('avatar');
         $avatarName = 'avatar_' . auth()->id() . '.' . $avatar->getClientOriginalExtension();
 
         // Delete existing avatar if it exists
         $user = auth('api')->user();
         if ($user->avatar) {
             Storage::disk('public')->delete($user->avatar);
         }
 
         // Store new avatar
         $avatar->storeAs('avatars', $avatarName, 'public');
 
         // Update user's avatar path in the database
         $user->avatar = 'avatars/' . $avatarName;
         $user->save();
         return jsonResponse(['avatar' => url('storage/avatars/' . $avatarName)], 200, true, 'با موفقیت بروزرسانی شد.', []);
     }
     return jsonResponse([], 422, false, 'تصویری ارسال نشده است', []);
 }
 /**
 * @OA\Put(
 *     path="/profile/update",
 *     summary="Update user data",
 *     tags={"Profile"},
 *     security={{ "bearerAuth":{} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="firstname", type="string"),
 *                 @OA\Property(property="lastname", type="string"),
 *                 @OA\Property(property="national_code", type="string"),
 *         
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت بروزرسانی شد."),
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation error message"),
 *          
 *         )
 *     )
 * )
 */

 public function updateUserData(Request $request){
    $validator = ValidationFeilds($request, __FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $user = auth('api')->user();

    // Update user data
    $user->update($request->all());
        // Return response
    return jsonResponse([$user], 200, true, 'با موفقیت بروزرسانی شد.', []);
    

 }


}