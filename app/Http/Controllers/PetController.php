<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    public function index()
    {
        $pets = Pet::with('category', 'tags', 'photoUrls')->get();
        return response()->json($pets);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'photoUrls' => 'array',
            'tags' => 'array'
        ]);

        $pet = Pet::create($validatedData);

        if ($request->has('photoUrls')) {
            foreach ($request->photoUrls as $url) {
                $pet->photoUrls()->create(['url' => $url]);
            }
        }

        if ($request->has('tags')) {
            $pet->tags()->attach($request->tags);
        }

        return response()->json($pet, 201);
    }

    public function show($id)
    {
        $pet = Pet::with('category', 'tags', 'photoUrls')->findOrFail($id);
        return response()->json($pet);
    }

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

        return response()->json($pet);
    }

    public function destroy($id)
    {
        $pet = Pet::findOrFail($id);
        $pet->delete();

        return response()->json(null, 204);
    }

    public function findByStatus(Request $request)
    {
        $status = $request->query('status');
        $pets = Pet::with('category', 'tags', 'photoUrls')->where('status', $status)->get();
        return response()->json($pets);
    }

    public function findByTags(Request $request)
    {
        $tags = $request->query('tags');
        $tagIds = Tag::whereIn('name', $tags)->pluck('id');
        $pets = Pet::with('category', 'tags', 'photoUrls')->whereHas('tags', function($query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        })->get();
        return response()->json($pets);
    }

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

        return response()->json($pet);
    }
}
