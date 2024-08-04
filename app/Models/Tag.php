<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string")
 * )
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function pets()
    {
        return $this->belongsToMany(Pet::class, 'pet_tags');
    }
}
