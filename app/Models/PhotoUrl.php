<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoUrl extends Model
{
    use HasFactory;

    protected $fillable = ['pet_id', 'url'];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }
}
