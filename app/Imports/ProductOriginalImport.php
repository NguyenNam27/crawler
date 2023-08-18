<?php

namespace App\Imports;

use App\Models\ProductOriginal;
use Maatwebsite\Excel\Concerns\ToModel;

class   ProductOriginalImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductOriginal([
            'code_product' => $row[1],
            'name' => $row[2],
            'price_cost' => $row[3],
            'link_product' => $row[4],
            'category_id' => $row[5],
            'status' => $row[6],
        ]);
    }
}
