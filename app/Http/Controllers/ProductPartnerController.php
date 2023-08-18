<?php

namespace App\Http\Controllers;

use App\Exports\ProductOriginalExport;
use App\Exports\ProductPartnerExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;

class ProductPartnerController extends Controller
{

    public function listProductPartner()
    {
        $partnerProductList = DB::table('product_partners')
            ->orderBy('id', 'desc')
            ->paginate(20);
        return view('productpartner.list', [
            'partnerProductList' => $partnerProductList
        ]);
    }

    public function comparePrices()
    {

    }

    public function exportProductPartner()
    {
        return Excel::download(new ProductPartnerExport, 'danhsachsanphamdoitac.xlsx');
    }
}
