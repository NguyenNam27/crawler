<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    public $timestamps=true;

    protected $table = 'partners';
    protected $fillable = ['name', 'url', 'category_id', 'keyword' , 'values','status'];

    protected $casts = [
        'values' => 'array',
    ];
}
