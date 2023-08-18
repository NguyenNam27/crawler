<?php

namespace App\Http\Controllers;

use App\Imports\ProductOriginalImport;
use App\Models\ProductOriginal;
use Illuminate\Support\Carbon;
use Faker\Core\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Excel;
use App\Exports\ProductOriginalExport;

class ProductOriginController extends Controller
{
    public function listProductOriginal(Request $request)
    {
        $search = $request->input('search');
        $originalSite = $request->input('mySiteOption', '');
        $now = Carbon::now()->format('Y-m-d');
        $listProductOriginal = DB::table('product_originals')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->when(!empty($originalSite), function ($query) use ($originalSite) {
                $query->where('product_originals.category_id', $originalSite);
            })
            ->orderBy('id', 'desc')
            ->groupBy(['code_product', 'brand'])

            ->paginate(20);

        $cate = ProductOriginal::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');

        return view('productoriginal.list', [
            'listProductOriginal' => $listProductOriginal,
            'cate' => $cate
        ]);
    }

    public function addProductOriginal()
    {
        return view('productoriginal.create');
    }

    public function saveProductOriginal(Request $request)
    {
        $data = array();
        $data['code_product'] = $request->code_product;
        $data['brand'] = $request->brand;
        $data['category_id'] = $request->category_id;
        $data['name'] = $request->name;
        $data['price_cost'] = $request->price_cost;
        $data['price_min'] = $request->price_min;
        $data['link_product'] = $request->link_product;
        $data['status'] = $request->status;
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        DB::table('product_originals')->insert($data);
        Session::put('message', 'Thêm sản phẩm gốc thành công');
        return Redirect::to('list-product-original');
    }

    public function editProductOriginal($id)
    {
        $edit_ProductOriginal = DB::table('product_originals')->where('id', $id)->first();

        return view('productoriginal.edit', [
            'edit_ProductOriginal' => $edit_ProductOriginal
        ]);
    }

    public function updateProductOriginal(Request $request, $id)
    {
        $data = array();
        $data['code_product'] = $request->code_product;
        $data['brand'] = $request->brand;
        $data['category_id'] = $request->category_id;
        $data['name'] = $request->name;
        $data['price_cost'] = $request->price_cost;
        $data['price_min'] = $request->price_min;
        $data['link_product'] = $request->link_product;
        $data['status'] = $request->status;
        DB::table('product_originals')->where('id', $id)->update($data);
        Session::put('message', 'Cập nhập sản phẩm gốc thành công');
        return Redirect::to('list-product-original');
    }

    public function delete_ProductOriginal($id)
    {
        DB::table('product_originals')->where('id', $id)->delete();
        Session::put('message', 'Xóa sản phẩm thành công');
        return Redirect::to('list-product-original');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportProductOrigin()
    {
        return Excel::download(new ProductOriginalExport, 'danhsachsanphambtp.xlsx');
    }

    public function importProductOriginal(Request $request)
    {
        Excel::import(new ProductOriginalImport, $request->file('file'));
        Session::put('message', 'Import file thành công');
        return Redirect::to('list-product-original');
    }

    public function importJsonfile(Request $request)
    {
        $jsonFile = $request->file('jsonfile');
        if ($jsonFile) {
            $getnameJsonfile = $jsonFile->getClientOriginalName();
            $jsonPath = storage_path('app/json/' . $getnameJsonfile);
            $jsonContents = json_decode(file_get_contents($jsonPath), true);
            foreach ($jsonContents as $data) {
                DB::table('product_originals')->insert(
                    array(
                    'code_product'=>$data['code_product'],
                    'name'=>$data['name'],
                    'price_cost'=>$data['price_cost'],
                    'category_id'=>$data['category_id'],
                    'status'=>$data['link_product'],
                    'link_product'=>$data['link_product'],
                    )
                );
            }
        }
    }
}
