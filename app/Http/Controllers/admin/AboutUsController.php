<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller\admin;
use Illuminate\Http\Request;
use App\Models\AboutUs;
class AboutUsController extends Controller
{
  
/**
 * @OA\Post(
 *     path="/aboutus",
 *     summary="Store a new aboutus",
 *     tags={"AboutUs"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="aboutus data",
 *         @OA\JsonContent(
 *             @OA\Property(property="content", type="string",example="Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia,molestiae quas vel sint commodi repudiandae consequuntur voluptatum laborum numquam blanditiis harum quisquam eius sed odit fugiat iusto fuga praesentium optio, eaque rerum!", description="The answer of the aboutus"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/AboutUs"),
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

public function StoreAboutUs(Request $request)
{
    $validator=ValidationFeilds($request,__FUNCTION__);
    if ($validator) {
        return $validator;
    }
    $aboutUs=AboutUs::first();
    if ($aboutUs) {
        return jsonResponse([], 400, true,  'یک نمونه وجود دارد .', []);
    }
    // Create the aboutuss
    $aboutUs = AboutUs::create($request->all());

    return jsonResponse($aboutUs, 200, true,  'با موفقیت ایجاد شد .', []);
}
/**
 * @OA\Get(
 *     path="/aboutus/{id}",
 *     summary="Get a single AboutUs by ID",
 *     tags={"AboutUs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the AboutUs to retrieve",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/AboutUs"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="aboutus not found",
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


public function singleAboutUs($id,Request $request) {
    $AboutUs = AboutUs::find($id);
    if (!$AboutUs) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    return jsonResponse($AboutUs->withJdateHuman(), 200, true, '', []);
}

/**
 * @OA\Put(
 *     path="/aboutus/{id}",
 *     summary="Update a aboutus by ID",
 *     tags={"AboutUs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the aboutus to update",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="aboutus data",
 *         @OA\JsonContent(

 *             @OA\Property(property="content", type="string",example="Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia,molestiae quas vel sint commodi repudiandae consequuntur voluptatum laborum numquam blanditiis harum quisquam eius sed odit fugiat iusto fuga praesentium optio, eaque rerum!", description="The content of the aboutus"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/AboutUs"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت به‌روزرسانی شد."),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="aboutus not found",
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
public function updateAboutUs($id,Request $request)
{
    $validator = ValidationFeilds($request, 'StoreAboutUs');
    if ($validator) {
        return $validator;
    }

    $aboutus = AboutUs::find($id);
    if (!$aboutus) {
        return jsonResponse([], 404, false, 'آیتم پیدا نشد.', []);
    }



    // Update course details with other fields
    $aboutus->update($request->all());

    return jsonResponse($aboutus, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
}


/**
 * @OA\Delete(
 *     path="/aboutus/{id}",
 *     summary="Delete a aboutus by ID",
 *     tags={"AboutUs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the aboutus to delete",
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
 *         description="aboutus not found",
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

public function destroyAboutUs($id,Request $request) {
    $AboutUs = AboutUs::find($id);
    if (!$AboutUs) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    
    $AboutUs->delete();
    return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
}
}
