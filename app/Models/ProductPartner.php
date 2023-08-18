<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPartner extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'product_partners';

    protected $fillable = ['code_product','name','partner_id','price_partner','price_sale','link_product','status','created_at'];
}
