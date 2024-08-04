<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Pet",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="category_id", type="integer"),
 *     @OA\Property(property="status", type="string"),
 * )
 */
class Pet extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photoUrls()
    {
        return $this->hasMany(PhotoUrl::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'pet_tags');
    }
}
