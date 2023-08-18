<?php

namespace App\Http\Controllers;

use App\Events\AddPartner;
use App\Models\Category;
use App\Models\Partner;
use App\Models\ProductPartner;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class PartnerController extends Controller
{
    public function listPartner()
    {
        $partnerList = DB::table('partners')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('partner.list', [
            'partnerList' => $partnerList
        ]);
    }

    public function addPartner()
    {
        return view('partner.create');
    }

    public function savePartner(Request $request)
    {
        $data = [
            'name' => $request->name,
            'url' => $request->url,
            'category_id' => $request->category_id,
            'keyword' => $request->keyword,
            'values' => [
                'class_parent' => $request->input('values_parent'),
                'class_name' => $request->input('values_name'),
                'class_price' => $request->input('values_price'),
                'class_sale' => $request->input('values_sale'),
                'class_link' => $request->input('values_link'),
                'class_code' => $request->input('values_code'),
            ],
        ];

        Partner::create($data);
        Session::put('message', 'Thêm đối tác thành công');
        return Redirect::to('list-partner');

    }

    public function edit_partner($id)
    {
        $edit_partner = DB::table('partners')->where('id', $id)->first();
        $jsonData = $edit_partner->values;
        $decodeData = json_decode($jsonData);

        return view('partner.edit', [
            'edit_partner' => $edit_partner,
            'decodeData' => $decodeData
        ]);
    }

    public function update_partner(Request $request, $id)
    {
        $data = [
            'name' => $request->name,
            'url' => $request->url,
            'keyword' => $request->keyword,
            'category_id' => $request->input('category_id'),
            'values' => [
                'class_parent' => $request->input('values_parent'),
                'class_name' => $request->input('values_name'),
                'class_price' => $request->input('values_price'),
                'class_sale' => $request->input('values_sale'),
                'class_link' => $request->input('values_link'),
                'class_code' => $request->input('values_code'),
            ],
            'status' => $request->status
        ];
        DB::table('partners')->where('id', $id)->update($data);
        Session::put('message', 'Cập nhập đối tác thành công');
        return Redirect::to('list-partner');
    }

    public function delete_partner($id)
    {
        DB::table('partners')->where('id', $id)->delete();
        Session::put('message', 'Xóa đối tác thành công');
        return Redirect::to('list-partner');
    }

    public function getProductCode($string)
    {
        $pattern = [
            '([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',
            '((?!\s)(TMP|BEP|MCP|MPP|MUP)[\-\s]{0,2}\d+)',
        ];
        preg_match_all('/' . implode('|', $pattern) . '/ui', $string, $m);

        return str_replace(' ', '', $m[0][0] ?? $m[1][0] ?? '');
    }

    public function crawl(Request $request, $id)
    {
        $partner = DB::table('partners')->where('id', $id)->first();
        if (!$partner) {
            return response('Partner is not exist', 400);
        }

        $values = json_decode($partner->values);

        $requestArr = [];
        $requestUrl = $partner->url;
        $requestKey = $partner->keyword;
        $categoryId = $partner->category_id;

        $requestArr['class_parent'] = $values->class_parent;
        $requestArr['class_name'] = $values->class_name;
        $requestArr['class_price'] = $values->class_price;
        $requestArr['class_sale'] = $values->class_sale;
        $requestArr['class_link'] = $values->class_link;
        $keywords = array_map('trim', explode(',', $requestKey));

        if (empty($keywords)) {
            Session::put('message', 'Từ khóa không hợp lệ');
            return redirect('/list-partner');
        }
        $count = 0;
        try {

            foreach ($keywords as $keyword):

//                $client = new Client();
                $client = new Client(HttpClient::create(['verify_peer' => false, 'verify_host' => false]));

                $url = $requestUrl . $keyword;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter($requestArr['class_parent']);
                $newProductsPartner = [];

                if (count($listItems) > 0) {
                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryId)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
//                $existData = [];
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryId, $existData, &$newProductsPartner, $requestArr, $partner) {
                                $name = $node->filter($requestArr['class_name'])->text();
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter($requestArr['class_price'])->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $sale = '';
                                if($requestArr['class_sale']) {
                                    $node->filter($requestArr['class_sale'])->each(function ($price) use (&$sale) {
                                        $sale = $price->text();
                                    });
                                }
                                $link_product = $node->filter($requestArr['class_link'])->attr('href');

                                $start_idx = strpos( $link_product, "https://");

                                if ($start_idx !== false ) {
                                    $link = $link_product;
                                }else{
                                    $link = $categoryId.$link_product;
                                }
                                $now = Carbon::now()->format('Y-m-d');
                                $productNameByDate = $name . '@@' . $now;
                                if (!in_array($productNameByDate, $existData)) {
                                    $newProductsPartner[] = [
                                        'code_product' => $code_product,
                                        'partner_id' => $partner->id,
                                        'name' => $name,
                                        'price_partner' => $price3,
                                        'price_sale' => $sale,
                                        'link_product' => $link,
                                        'category_id' => $categoryId,
                                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    ];
                                }
                            }
                        );
                        ProductPartner::insert($newProductsPartner);
                        DB::commit();

                        $count += count($newProductsPartner);
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }

            endforeach;
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
        Session::put('message', 'CRAWL DATA SUCCESSFULLY');
//        {'.$count.' SP}
        return redirect('/list-partner?page=' . (\request('page',1)));
    }
}
