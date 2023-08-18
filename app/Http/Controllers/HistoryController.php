<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductOriginal;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function getListHistory(Request $request)
    {

        $originalSite = $request->input('mySiteOption');

        $search = $request->input('search');
        $sites = Product::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $cate = ProductOriginal::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $products = Product::selectRaw("name, DATE_FORMAT(created_at, '%Y-%d-%m') as unique_product_by_date, price_cost, category_id, code_product, link_product")
            ->where('code_product', 'like', '%' . $search . '%')
            ->orderBy('created_at', 'desc')
            ->get();
        $result = [];
        // sites gốc
        $originalSites = [
            'https://junger.vn',
            'https://hawonkoo.vn',
            'https://poongsankorea.vn',
            'https://bossmassage.vn'
        ];

        // Site gốc nên giá cần so sánh với nhau, nên k hiển thị
        foreach ($sites as $key => $site) {
            if (in_array($site, $originalSites)) {
                $sites->forget($key);
            }
        }
        //Unique: (code, created_at)
        //for toàn bộ
        foreach ($products->groupBy('code_product') as $code => $productByCode) {
            foreach ($productByCode->groupBy('unique_product_by_date') as $date => $productsByDate) {
                $originalName = '';
                $originalPrice = 0;
                $originalProductLink = '';
                // tìm giá gốc và link gốc
                foreach ($productsByDate as $key => $productByDate) {
                    if ($productByDate['category_id'] === $originalSite) {

                        $originalName = $productByDate['name'];

                        $originalPrice = (float)$productByDate['price_cost'];

                        $originalProductLink = $productByDate['link_product'];
                        unset($productsByDate[$key]);
                    }
                    // do original sites chung 1 giá thì k cần so sánh, nên loại bỏ
                    if (in_array($productByDate['category_id'], $originalSites)) {
                        unset($productsByDate[$key]);
                    }
                }

                // so sánh giá với các site khác
                $items = [];
                foreach ($productsByDate as $productByDate) {
                    $items[] = [
                        'category_id' => $productByDate['category_id'],
                        'price' => (float)$productByDate['price_cost'],
                        'price_diff' => $originalPrice - (float)$productByDate['price_cost'],
                        'link_product' => $productByDate['link_product'],
                    ];
                }

                $result[] = [
                    'code' => $code,
                    'created_at' => $date,
                    'originalName' => $originalName,
                    'original_price' => $originalPrice,
                    'items' => $items,
                    'code_product' => isset($productByDate['code_product']) ? $productByDate['code_product'] : 'EMPTY',
                    'link_product' => $originalProductLink,
                ];
            }

        }

        $total = count($result);
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10000);
        $offset = ($page - 1) * $limit;
        $resultPaginate = array_slice($result, $offset, $limit);
        return view('history.list', [
            'products' => $resultPaginate,
            'total' => $total,
            'count_site' => $sites->count(),
            'sites' => $sites,
            'cate' => $cate
        ]);
    }
}
