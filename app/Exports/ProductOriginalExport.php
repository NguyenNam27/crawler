<?php

namespace App\Exports;

use App\Models\ProductOriginal;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductOriginalExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ProductOriginal::all();
    }
}
