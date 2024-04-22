<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Faq;
class FAQController
{
   /**
 * @OA\Get(
 *     path="/faqs",
 *     summary="Get faqs with pagination",
 *     tags={"Faqs"},
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
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Faqs")),
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


 public function getFaqs(Request $request) {
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');
    $user_id = $request->input('user_id');
    $read = $request->input('read');
    // Start building the query
    $query = Faq::query();
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('question', 'like', '%' . $search . '%');
            $q->orWhere('answer', 'like', '%' . $search . '%');
            
        });
    }
    // Execute the query and paginate the results
    $faqs = $query->paginate($perPage, ['*'], 'page', $page);
    $transformedFaq = $faqs->map(function ($faq) {
        return $faq->withJdateHuman();
    });
    return jRWithPagination($faqs, $transformedFaq, 200, true, '', []);
}

/**
 * @OA\Get(
 *     path="/faqs/{id}",
 *     summary="Get a single faq by ID",
 *     tags={"Faqs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the faq to retrieve",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/Faqs"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Faq not found",
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


public function singleFaq($id,Request $request) {
    $Faqs = Faq::find($id);
    if (!$Faqs) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    return jsonResponse($Faqs->withJdateHuman(), 200, true, '', []);
}



}
