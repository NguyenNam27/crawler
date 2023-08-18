<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOriginal extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'product_originals';
    protected $fillable = ['code_product','name','brand','price_cost','price_min','link_product','category_id','status','created_at'];

    public function brand(){
        $this->belongsTo(Brand::class,'brand','id');
    }
}
