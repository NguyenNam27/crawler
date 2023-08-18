<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProductReportExcel extends ProductReport
{

    protected $appends = array('availability');
    public function getAvailabilityAttribute()
    {
        return '';
    }

    public function getProductPartner()
    {
        return $this->hasMany(ProductPartner::class, 'code_product', 'code_product')
            ->whereDate('product_partners.created_at', Carbon::now())
            ->orderBy('product_partners.created_at', 'DESC')
            ->groupBy('product_partners.partner_id')
            ->get()
            ;
    }
}
