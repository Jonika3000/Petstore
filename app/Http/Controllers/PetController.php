<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(title="PetStore API", version="1.0")
 */
class PetController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pets",
     *     summary="Get list of pets",
     *     tags={"Pets"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Pet"))
     *     )
     * )
     */
    public function index()
    {
        $pets = Pet::with('category', 'tags', 'photoUrls')->get();
        return response()->json($pets);
    }

    /**
     * @OA\Post(
     *     path="/api/pets",
     *     summary="Add a new pet to the store",
     *     tags={"Pets"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category", "name", "status"},
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the category"),
     *                 @OA\Property(property="name", type="string", description="Name of the category")
     *             ),
     *             @OA\Property(property="name", type="string", description="Name of the pet"),
     *             @OA\Property(property="status", type="string", description="Status of the pet"),
     *             @OA\Property(property="photoUrls", type="array", @OA\Items(type="string"), description="List of photo URLs"),
     *             @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"), description="List of tags")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pet created",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category' => 'required|array',
            'category.id' => 'required|exists:categories,id',
            'category.name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'photoUrls' => 'array',
            'tags' => 'array'
        ]);

        $pet = Pet::create([
            'category_id' => $validatedData['category']['id'],
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
        ]);

        if ($request->has('photoUrls')) {
            foreach ($request->photoUrls as $url) {
                $pet->photoUrls()->create(['url' => $url]);
            }
        }

        if ($request->has('tags')) {
            $pet->tags()->attach($request->tags);
        }

        return response()->json($pet->load('category', 'tags', 'photoUrls'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/pets/{id}",
     *     summary="Find pet by ID",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pet to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     )
     * )
     */
    public function show($id)
    {
        $pet = Pet::with('category', 'tags', 'photoUrls')->findOrFail($id);
        return response()->json($pet);
    }

    /**
     * @OA\Put(
     *     path="/api/pets/{id}",
     *     summary="Update an existing pet",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pet to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the category"),
     *                 @OA\Property(property="name", type="string", description="Name of the category")
     *             ),
     *             @OA\Property(property="name", type="string", description="Name of the pet"),
     *             @OA\Property(property="status", type="string", description="Status of the pet"),
     *             @OA\Property(property="photoUrls", type="array", @OA\Items(type="string"), description="List of photo URLs"),
     *             @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"), description="List of tags")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet updated",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'status' => 'string|max:50',
            'photoUrls' => 'array',
            'tags' => 'array'
        ]);

        $pet = Pet::findOrFail($id);
        $pet->update($validatedData);

        if ($request->has('photoUrls')) {
            $pet->photoUrls()->delete();
            foreach ($request->photoUrls as $url) {
                $pet->photoUrls()->create(['url' => $url]);
            }
        }

        if ($request->has('tags')) {
            $pet->tags()->sync($request->tags);
        }

        return response()->json($pet->load('category', 'tags', 'photoUrls'));
    }

    /**
     * @OA\Delete(
     *     path="/api/pets/{id}",
     *     summary="Deletes a pet",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pet to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Pet deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $pet = Pet::findOrFail($id);
        $pet->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/pets/status",
     *     summary="Find pets by status",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description="Status to filter pets",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Pet"))
     *     )
     * )
     */
    public function findByStatus(Request $request)
    {
        $status = $request->query('status');
        $pets = Pet::with('category', 'tags', 'photoUrls')->where('status', $status)->get();
        return response()->json($pets);
    }

    /**
     * @OA\Get(
     *     path="/api/pets/tags",
     *     summary="Find pets by tags",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         required=true,
     *         description="Comma-separated list of tag names to filter pets",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Pet"))
     *     )
     * )
     */
    public function findByTags(Request $request)
    {
        $tags = $request->query('tags');
        $tagIds = Tag::whereIn('name', explode(',', $tags))->pluck('id');
        $pets = Pet::with('category', 'tags', 'photoUrls')
            ->whereHas('tags', function($query) use ($tagIds) {
                $query->whereIn('tags.id', $tagIds);
            })
            ->get();
        return response()->json($pets);
    }

    /**
     * @OA\Post(
     *     path="/api/pets/{petId}/uploadImage",
     *     summary="Uploads an image",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="petId",
     *         in="path",
     *         required=true,
     *         description="ID of the pet to upload image",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file to upload")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Image uploaded",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", description="URL of the uploaded image")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Image upload failed"
     *     )
     * )
     */
    public function uploadImage(Request $request, $petId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $pet = Pet::findOrFail($petId);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('images', 'public');

            $pet->photoUrls()->create(['url' => Storage::url($path)]);

            return response()->json(['url' => Storage::url($path)], 201);
        }

        return response()->json(['error' => 'Image upload failed'], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/pets/{petId}",
     *     summary="Updates a pet in the store with form data",
     *     tags={"Pets"},
     *     @OA\Parameter(
     *         name="petId",
     *         in="path",
     *         required=true,
     *         description="ID of the pet to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the pet"),
     *             @OA\Property(property="status", type="string", description="Status of the pet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet updated",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     )
     * )
     */
    public function updateWithForm(Request $request, $petId)
    {
        $pet = Pet::findOrFail($petId);

        if ($request->has('name')) {
            $pet->name = $request->input('name');
        }

        if ($request->has('status')) {
            $pet->status = $request->input('status');
        }

        $pet->save();

        return response()->json($pet->load('category', 'tags', 'photoUrls'));
    }
}
