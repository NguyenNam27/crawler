<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    public $timestamps=true;

    protected $table = 'brands';
    protected $fillable = ['code', 'name'];

    public function productOrigins(){
        $this->hasMany(ProductOriginal::class,'brand','id');
    }
}
