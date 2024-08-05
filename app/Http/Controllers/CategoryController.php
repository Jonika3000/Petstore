<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get list of categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Category"))
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Add a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *       required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(property="id", type="integer", description="ID of the category"),
     *                  @OA\Property(property="name", type="string", description="Name of the category")
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create($validatedData);
        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Find category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update an existing category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *                   @OA\JsonContent(
     *                   @OA\Property(property="id", type="integer", description="ID of the category"),
     *                   @OA\Property(property="name", type="string", description="Name of the category")
     *           )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update($validatedData);
        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Deletes a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
