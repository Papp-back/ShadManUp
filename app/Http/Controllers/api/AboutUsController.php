<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\AboutUs;
class AboutUsController
{
    /**
 * @OA\Get(
 *     path="/aboutus",
 *     summary="Get AboutUs",
 *     tags={"AboutUs"},
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


public function singleAboutUs(Request $request) {
    $AboutUs = AboutUs::first();
    if (!$AboutUs) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    return jsonResponse($AboutUs->withJdateHuman(), 200, true, '', []);
}
}
