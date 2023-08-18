<?php

namespace App\Exports;

use App\Models\ProductPartner;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductPartnerExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ProductPartner::all();
    }
}
