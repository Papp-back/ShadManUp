<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller\admin;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{


    /**
 * @OA\Get(
 *     path="/categories",
 *     summary="Get categories with pagination",
 *     tags={"Category"},
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
 *         name="level",
 *         in="query",
 *         description="level query",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category")),
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

    public function getCategories(Request $request) {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $level = $request->input('level');
        // Start building the query
        $query = Category::query()->with('children')->with('parent');
        if ($level) {
            $query->where('level', $level);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
                
            });
        }
        
        // Execute the query and paginate the results
        $categories = $query->paginate($perPage, ['*'], 'page', $page);
        $transformedCategories = $categories->map(function ($Category) {
            $parent=Category::find($Category->parent_id);
            // $Category->parent=null;
            // if ($parent) {
            //     $Category->parent=[
            //         'id'=>$parent->id||null,
            //         'name'=>$parent->name||null,
            //     ];
            // }
            return $Category->withJdateHuman();
        });
    
      
        return jRWithPagination($categories, $transformedCategories, 200, true, '', []);
    }
/**
 * @OA\Post(
 *     path="/categories",
 *     summary="Store a new category",
 *     tags={"Category"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","parent_id"},
 *             @OA\Property(property="name", type="string", example="دسته اصلی"),
 *             @OA\Property(property="parent_id", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *              @OA\Property(property="data", type="object", ref="#/components/schemas/Category"),
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
 *         response=404,
 *         description="ValidationError",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items()),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="دسته والد وجود ندارد!"),
 *             @OA\Property(property="errors", type="array", @OA\Items()),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

    public function StoreCategory(Request $request)
    {
        $validator=ValidationFeilds($request,__FUNCTION__);
        if ($validator) {
            return $validator;
        }
        $category=Category::where('id',$request->input('parent_id'))->first();
        if (!$category && $request->input('parent_id')!='0') {
            return jsonResponse([], 404, false,'دسته والد وجود ندارد!', []);
        }
        $parent_id = $request->input('parent_id') === '0' ? null : $request->input('parent_id');
        $level = $parent_id ? Category::find($parent_id)->level + 1 : 1;
        
        $categoryData = $request->all();
        $categoryData['parent_id'] = $parent_id;
        $categoryData['level'] = $level;
        
        $category = Category::create($categoryData);
    
        return jsonResponse($category, 200, true,  'با موفقیت ایجاد شد .', []);
    }

        /**
 * @OA\Get(
 *     path="/categories/{id}",
 *     summary="Retrieve a single category by ID",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the category",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/Category"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example=""),
 *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
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

 public function singleCategory($id,Request $request) {
    $category = Category::find($id);
    if (!$category) {
        return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
    }
    $parent=Category::find($category->parent_id);
    $category->parent=[
        'id'=>isset($parent->id)?$parent->id:0,
        'name'=>isset($parent->name)?$parent->name:'دسته بندی مادر',
    ];
    return jsonResponse($category->withJdateHuman(), 200, true, '', []);
}
/**
 * @OA\Put(
 *     path="/categories/{id}",
 *     summary="Update a category by ID",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the category",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\requestBody(
 *         @OA\JsonContent(
 *             required={"name", "parent_id"},
 *             @OA\Property(property="name", type="string", example="Updated Category"),
 *             @OA\Property(property="parent_id", type="integer", format="int64", example="0"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", ref="#/components/schemas/Category"),
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="با موفقیت به‌روزرسانی شد."),
 *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
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
 *     @OA\Response(
 *         response=404,
 *         description="Category not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Category not found"),
 *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
 *         ),
 *     ),
 *     security={{"bearerAuth": {}}},
 * )
 */

    public function updateCategory($id,Request $request)
    {
        $validator = ValidationFeilds($request, __FUNCTION__);
        if ($validator) {
            return $validator;
        }
        if ($request->input('parent_id')==$id) {
            return jsonResponse([], 404, false,'شناسه دسته بندی با شناسه والد نمیتواند برابر باشد .', []);
        }
        $category_parent=Category::where('id',$request->input('parent_id'))->first();
        if (!$category_parent && $request->input('parent_id')!='0') {
            return jsonResponse([], 404, false,'دسته والد وجود ندارد!', []);
        }
        $category=Category::find($id);
        if (!$category) {
            return jsonResponse($category, 404, false, 'آیتم وجود ندارد .', []);
        }
        $level = $request->input('parent_id') ? Category::find($request->input('parent_id'))->level + 1 : 1;
        $category->update(array_merge($request->all(), ['level' => $level]));

        return jsonResponse($category, 200, true, 'با موفقیت به‌روزرسانی شد.', []);
    }
    /**
 * @OA\Delete(
 *     path="/categories/{id}",
 *     summary="Delete a category by ID",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the category",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Category deleted successfully",
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
 *         description="Category not found",
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

    public function destroyCategory($id,Request $request) {
        $category = Category::find($id);
        if (!$category) {
            return jsonResponse([], 404, false, 'آیتم وجود ندارد .', []);
        }
        // Use a recursive function to delete children
        $this->deleteCategoryAndChildren($category);
        return jsonResponse([], 200, true, 'با موفقیت حذف شد.', []);
    }
    private function deleteCategoryAndChildren($category)
    {
        // Delete children recursively
        foreach ($category->children as $child) {
            $this->deleteCategoryAndChildren($child);
        }
    
        // Delete the category
        $category->delete();
    }
}