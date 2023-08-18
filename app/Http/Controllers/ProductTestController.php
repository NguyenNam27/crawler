<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductOriginal;
use App\Models\Product;
use App\Models\ProductPartner;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

//use mysql_xdevapi\Exception;
use Symfony\Component\DomCrawler\Crawler;

class ProductTestController extends Controller
{
    public function getProductCode($string)
    {
        $pattern = [
            '([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',
            '((?!\s)(TMP|BEP|MCP|MPP|MUP)[\-\s]{0,2}\d+)',
        ];
        preg_match_all('/' . implode('|', $pattern) . '/ui', $string, $m);

        return str_replace(' ', '', $m[0][0] ?? $m[1][0] ?? '');
    }

    public function Sendo()
    {
        try {
            $urlApiArr = array(
                'https://searchlist-api.sendo.vn/web/products?q=hawonkoo&platform=web&page=1&size=60&sortType=rank&search_type=&app_ver=2.32.12&track_id=c6ccd1ce-94ed-4422-9cca-ddd191753f3b&search_suggestion_list=&search_textbox_string=&click_suggestion_index=',
                'https://searchlist-api.sendo.vn/web/products?q=junger&platform=web&page=1&size=60&sortType=rank&search_type=&app_ver=2.32.13&track_id=c6ccd1ce-94ed-4422-9cca-ddd191753f3b&search_suggestion_list=&search_textbox_string=&click_suggestion_index=',
                'https://shop-home.sendo.vn/api/v1/product/filter?platform=6&seller_admin_id=871356&sortType=norder_30_desc&limit=24&page=1'
            );

//            $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
//                ->where('category_id', $urlArr)
//                ->get()
//                ->pluck('unique_product_by_date')
//                ->toArray();

            foreach ($urlApiArr as $Arr) {
                $totalItem = 0;
                $curl = curl_init();
                //đặt tuỳ chọn cấu hình cURL
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $Arr, //đường dẫn url cần xử lý

                    CURLOPT_RETURNTRANSFER => true, //nếu true thì sẽ trả kết quả về ở hàm curl_exec nên ta phải echo kết quả đó mới in lên trình duyệt, nếu false thì thực thi là nó in kết quả lên trình duyệt luôn
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,//số lương lớn nhất redirects được điều khiển bằng Option CURLOPT_MAXREDIRS
                    CURLOPT_TIMEOUT => 0,//thiết lập thời gian sống của một request
                    CURLOPT_FOLLOWLOCATION => true, //điều hướng xử lý cURL
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    //phương thức
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    //thiet lap header request
                    CURLOPT_HTTPHEADER => array(
                        'Origin: https://www.sendo.vn',
                        'Pragma: no-cache',
                        'Referer: https://www.sendo.vn/',
                        'Sec-Ch-Ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
                        'Sec-Ch-Ua-Mobile: ?0',
                        'Sec-Ch-Ua-Platform: "Windows"',
                        'Sec-Fetch-Dest: empty',
                        'Sec-Fetch-Mode: cors',
                        'Sec-Fetch-Site: same-site',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36:'
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $de_response = json_decode($response, true);

                if (!empty($de_response['data'])) {
                    foreach ($de_response['data'] as $result) {
                        $urlL = 'https://www.sendo.vn/';
                        $code_product2 = $this->getProductCode($result['name']);
                        if ($code_product2 == "") {
                            continue;
                        }

                        $result[] = [
                            'name' => $result['name'],
                            'price' => $result['default_price_max'],
                            'link' => 'https://www.sendo.vn/' . $result['category_path']
                        ];

                        $value = new ProductPartner();
                        $value->code_product = $code_product2;
                        $value->name = $result['name'];
                        $value->price_partner = $result['default_price_max'];
                        $value->price_sale = $result['sale_price_max'];
                        $value->link_product = 'https://www.sendo.vn/'.$result['category_path'];
                        $value->category_id = $urlL;
                        $value->save();
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart fail: {$exception->getMessage()}");
        }
    }

    public function shoppe()
    {
        $curlShoppe = curl_init();

        curl_setopt_array($curlShoppe, array(
            CURLOPT_URL => 'https://shopee.vn/api/v4/search/search_items?by=relevancy&entry_point=ShopBySearch&extra_params=%7B%22global_search_session_id%22%3A%22gs-f8811b05-d54f-46c0-9b50-e1f11c235d9a%22%2C%22search_session_id%22%3A%22ss-9285b5a2-69a9-4e23-b692-7b55bbf89d36%22%7D&keyword=hawonkoo&limit=60&match_id=837802110&newest=0&order=desc&page_type=shop&scenario=PAGE_SHOP_SEARCH&version=2&view_session_id=37f7f212-e113-4851-b6f9-564cdc8e8544',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 20,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
//                'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvcmVmcmVzaCIsImlhdCI6MTY4MDIzMjM0MCwiZXhwIjoxNjgwMjM2MDE1LCJuYmYiOjE2ODAyMzI0MTUsImp0aSI6Ik1Nb0Vscm9QS2NaT2dlckgiLCJzdWIiOiIxOCIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.MrXoxalfB1CU1g8d4FrBzsdVDRGpZDEWGZAQSGv52Nc',
//                'Cookie: SPC_R_T_ID=cK0i3m94m+eb+kMlBLLkYZca2ALGac2EcO2VTq+JgKV0YrHD2OWX2KNJpxmwQwYJkGblW5Dv1mheSf+mqnQyPJB3xcqL3PjLKqXatW5fvaT5aTRUokAQ+GP6/QrUbEekbgwEoor0Y6JzOS+hcOBCXYRIPe/4KLBA1Tbnzi1Ky5w=; SPC_R_T_IV=TmowMHpiWGppTWZYMFBEeg==; SPC_SI=26+aZAAAAABaSXR0eDdXaC8dwQEAAAAAVk1iZ2FVQkU=; SPC_T_ID=cK0i3m94m+eb+kMlBLLkYZca2ALGac2EcO2VTq+JgKV0YrHD2OWX2KNJpxmwQwYJkGblW5Dv1mheSf+mqnQyPJB3xcqL3PjLKqXatW5fvaT5aTRUokAQ+GP6/QrUbEekbgwEoor0Y6JzOS+hcOBCXYRIPe/4KLBA1Tbnzi1Ky5w=; SPC_T_IV=TmowMHpiWGppTWZYMFBEeg==',
//                'Scheme: https',
//                'Connection: keep-alive',
                'A38d12e2: ',
                'Accept: application/json',
                'Accept-Encoding: gzip, deflate, br',
                'Accept-Language: en-US,en;q=0.9,vi;q=0.8,zh-CN;q=0.7,zh;q=0.6',
                'Af-Ac-Enc-Dat: AAcyLjkuMS0yAAABiT8+ElUAABDCAzAAAAAAAAAAAi465FfY7rfomxiOdYUxQAc2wECAMiReXyNAO33j7+ykZR18QDPdApjbMkGSlSEq8KfAy0nbqwdDCFhLGqI0KqKlE3i0vmwA3JT35BN22bj+JMzwE5f/NhMnw8f21UXD2mkb2JtJZxHpNhUrIr53t3AlZaCY+qbllAwjUJ1rNYEEhZMktHsoyfpI82TxxYILTF5RA89kiQoZj//zLtnE/3H1zLnIQrSqTKv7pFY6lNtq1XnD5myhAs/1ynOyR474MoFA1wvwHGW/qArOU9gpoYUzePJTiztEiQcyoRDreE6BbnZtiLpgzi5OWUxLDJJkD9BI+fsmH0ChJDrPql4eKpam5QrWZ/Wn+Elxqc+UCJARZRTdMMU2zvKouGAw1F9Gxf9rmUQtkXHPxfnveu+EvcvtwBeVmmE+/S8PYcoc69CGO87wcNIeStZklzrIIgLUG2RN4+tvkWB5sqo7ZX30EEOlPpuedvrx2cDNAh/WiazMgcgB2IpWoI3lzvoPKDx96WVQBxHfn3ef+xokLITc0bqLihdVUQJfq3K1AG1EpKO616ktiwbY7P9KHML6aQtF4FlKd6xjdVpA6a7RMwH/8HLpttFgUQJfq3K1AG1EpKO616ktiwbY7P9KHML6aQtF4FlKd6wdA86fQ2w9qjRqJCAkbVT4rpOskOT93hbrDw1V+S5ojQsNXts5GyAjI8vnGItu2NNhh/wm6gMaLnkSZfhLSUaJN5NwO1tj5RgI6LeacDwoJwsNXts5GyAjI8vnGItu2NOayU0XVJ4Y7t/pjXd1QJM6l/82EyfDx/bVRcPaaRvYm5f/NhMnw8f21UXD2mkb2Juzj2owmcpkgc3OTW18AOMqfAgRAk5cQXhEThX2CLZnn9tYeDqRpl7n1jIdci2ErC4djl95oPLXXpA3IyLPKxBHJQrrB0BDzEf0OU/y+aydYX0/UJk8yPek6xqzZZxWvVoAMQtOj/N6mIrG7dhZvrbCOStPWO5qmVLNyVeYQ4rGcnXp2NzYZusDaGPMKmS+kNHdISJR/kC12g6YaiCMOY9Y98jZ/901tpYwiYTjGF/2xJq+Re2e8YHn4DkAkTxcMyY=',
                'Af-Ac-Enc-Sz-Token: Fi2h2sGUvwEoqhSxpX2vog==|Hw7GjY/Nmqi/9u1xF1zZgAHZUPGNz7f7jUmpGA1vrN2a74BaApfJmj+MNeOwlhxlqfZr/7WNIOaECmi+ycngX7dnLRCthu76yts=|NKzvTPV0VWDO7inn|06|3',
                'Content-Type: application/json',
                'Cookie: SPC_F=gv0nvEIn3P3V60Q8hRMKx564283Q3sW5; REC_T_ID=189c3c1e-0f3f-11ee-b212-347379167e78; SPC_CLIENTID=Z3YwbnZFSW4zUDNWyqqqddrboezkhpcx; __LOCALE__null=VN; csrftoken=nFGxf0CtBNR1fst5Kpl77yMRyQXCe6a7; SPC_EC=-; SPC_U=-; SPC_SI=26+aZAAAAABaSXR0eDdXaC8dwQEAAAAAVk1iZ2FVQkU=; SPC_R_T_ID=cK0i3m94m+eb+kMlBLLkYZca2ALGac2EcO2VTq+JgKV0YrHD2OWX2KNJpxmwQwYJkGblW5Dv1mheSf+mqnQyPJB3xcqL3PjLKqXatW5fvaT5aTRUokAQ+GP6/QrUbEekbgwEoor0Y6JzOS+hcOBCXYRIPe/4KLBA1Tbnzi1Ky5w=; SPC_R_T_IV=TmowMHpiWGppTWZYMFBEeg==; SPC_T_ID=cK0i3m94m+eb+kMlBLLkYZca2ALGac2EcO2VTq+JgKV0YrHD2OWX2KNJpxmwQwYJkGblW5Dv1mheSf+mqnQyPJB3xcqL3PjLKqXatW5fvaT5aTRUokAQ+GP6/QrUbEekbgwEoor0Y6JzOS+hcOBCXYRIPe/4KLBA1Tbnzi1Ky5w=; SPC_T_IV=TmowMHpiWGppTWZYMFBEeg==; _QPWSDCXHZQA=520f2f8b-fac3-4d21-ea06-2137947a0cac; shopee_webUnique_ccd=Fi2h2sGUvwEoqhSxpX2vog%3D%3D%7CHw7GjY%2FNmqi%2F9u1xF1zZgAHZUPGNz7f7jUmpGA1vrN2a74BaApfJmj%2BMNeOwlhxlqfZr%2F7WNIOaECmi%2BycngX7dnLRCthu76yts%3D%7CNKzvTPV0VWDO7inn%7C06%7C3; ds=2bd8309b4463b4370852facc22ca273a',
                'Referer: https://shopee.vn/search?keyword=hawonkoo&shop=837802110&trackingId=searchhint-1688983138-63be2bbc-1f08-11ee-8df0-9440c94484cc',
                'Sec-Ch-Ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
                'Sec-Ch-Ua-Mobile: ?0',
                'Sec-Ch-Ua-Platform: "Windows"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                'X-Api-Source: pc',
                'X-Csrftoken: nFGxf0CtBNR1fst5Kpl77yMRyQXCe6a7',
                'X-Requested-With: XMLHttpRequest',
                'X-Sap-Access-F: 3.2.114.2.0|13|2.9.1-2_5.1.0_0_352|025e291f934b4ff6877e81362df688426f60ccbf1f2d4f|10900|1100',
                'X-Sap-Access-S: eG6rtJRG75H2yDHEFyQsF5LlqRCu3XnxjKbQsozrGBs=',
                'X-Sap-Access-T: 1688983179',
                'X-Sap-Ri: 8cd6ab643f53f35c4044d83bd0473e4fb7d2fde0bfa75ead',
                'X-Shopee-Language: vi',
                'X-Sz-Sdk-Version: 2.9.1-2&1.4.1',
                '3bb61e75: T]hZC$\RgL+snrf/Ob7olegQf',
                '48f6c5cf: .kA-7oEE=bcVI5l8\'=5`G29OP'
            )
        ));
        $response = curl_exec($curlShoppe);
        curl_close($curlShoppe);
        echo '<pre>';
        echo $response;
        echo '</pre>';
    }

    public function mediaMart()
    {
        try {
            $urlArr = array(
                'https://mediamart.vn/tag?key=hawonkoo',
                'https://mediamart.vn/tag?key=junger',
                'https://mediamart.vn/tag?key=boss',
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.product-list .card');
                $categoryUrl = 'https://mediamart.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);
                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://mediamart.vn';
                                $name = $node->filter('p.product-name')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter('p.product-price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('p.product-promotionshort')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a.product-item')->attr('href');
                                $link = $categoryUrl . $link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {

                                    $newProducts[] = [
                                        'code_product' => $code_product,
                                        'name' => $name,
                                        'price_partner' => $price3,
                                        'price_sale' => $sale,
                                        'category_id' => $categoryUrl,
                                        'link_product' => $link,
                                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    ];
//                                }
                            });
//                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function dienMayXanh()
    {
        try {
            $urlArr = array(
                'https://www.dienmayxanh.com/search?key=junger',
                'https://www.dienmayxanh.com/search?key=hawonkoo',
                'https://www.dienmayxanh.com/search?key=poongsan',
                'https://www.dienmayxanh.com/search?key=qu%e1%ba%a1t+boss#c=7498&kw=qu%E1%BA%A1t%20boss&pi=0'

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('ul.listproduct li.item');
                $DMXUrl = 'https://www.dienmayxanh.com';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);
                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $DMXUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($DMXUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://www.dienmayxanh.com';
                                $name = $node->filter('a h3')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter('.price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('p.item-gift')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a.main-contain')->attr('href');
                                $link = $DMXUrl . $link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {

                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $DMXUrl,
                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
//                                }
                            });
//                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }

    }
    public function pico(){
        try {
            $urlArr = array(
                'https://pico.vn/brand/hawonkoo',
                'https://pico.vn/brand/junger',
                'https://pico.vn/brand/boss',
                'https://www.dienmayxanh.com/search?key=qu%e1%ba%a1t+boss#c=7498&kw=qu%E1%BA%A1t%20boss&pi=0'

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('#js-prod-list .p-item');
                $DMXUrl = 'https://pico.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);
                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $DMXUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($DMXUrl, &$existData, &$newProducts) {
                                $picoUrl = 'https://pico.vn';
                                $name = $node->filter('.p-text .p-name')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }

                                $price = 0;
                                $node->filter('.p-price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('.p-offer-group')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $link = $DMXUrl . $link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $picoUrl,
                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
//                                }
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function kitchenStore(){
        try {
            $urlArr = array(
                'https://kitchenstore.com.vn/tim-kiem.html?key=junger',
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.category__product-list ul li');
                $categoryUrl = 'https://kitchenstore.com.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://kitchenstore.com.vn';
                                $name = $node->filter('.name')->text();
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter('.sale-price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                $node->filter('p.product-promotionshort')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                $link_product = $node->filter('a')->attr('href');
                                $link = $link_product;
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                                }
                            });
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function nguyenKim(){
        try {
            $urlArr = array(
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo',
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo&trang=2',
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo&trang=3',
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=junger',
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=boss',
                'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=poongsan'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.nk-result-grid a');
                $categoryUrl = 'https://www.nguyenkim.com';
//                dd($listItems);
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://www.nguyenkim.com';
                                $name = $node->filter('.product-render')->attr('name');
                                echo '<pre>';
                                print_r($name);
                                echo '<pre>';
                                $code_product =                                                                                                                                                                                                                                                                                                                                                                                                                                              $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '<pre>';
                                $price = 0;
                                $node->filter('.product-body .product-price > p')->each(function ($item) use (&$price) {
//                                    .product-render .product-body .product-price > p
                                    $price = $item->text();
                                    var_dump($price);
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price);
                                echo '<pre>';
//                                $node->filter('')->each(function ($price) use (&$sale) {
//                                    $sale = $price->text();
//                                });
                                $link_product = $node->filter('.product-render')->attr('href');
                                $link = $link_product;
                                echo '<pre>';
                                print_r($link);
                                echo '<pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
//                                    'price_partner' => $price3,
//                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
//                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
//                                }
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function khanhvyhome(){
        try {
            $urlArr = array(
                'https://khanhvyhome.com.vn/index.php?route=product/search&search=junger&limit=100&',
                'https://khanhvyhome.com.vn/index.php?route=product/search&search=hawonkoo&limit=100&'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.row .product-layout');
                $categoryUrl = 'https://khanhvyhome.com.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://khanhvyhome.com.vn';
                                $name = $node->filter('.product_name')->text();
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter('.price-new')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('.text_product_loop_have_km')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                $link_product = $node->filter('a')->attr('href');
                                $link = $link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
//                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
//                                }
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function dienmaygiakhang(){

        try {
            $urlArr = array(
                'https://dienmaygiakhang.vn/?s=hawonko&post_type=product',
                'https://dienmaygiakhang.vn/?s=junger&post_type=product',
                'https://dienmaygiakhang.vn/?s=qu%E1%BA%A1t+boss&post_type=product',
                'https://dienmaygiakhang.vn/?s=poongsan&post_type=product'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.box');
                $categoryUrl = 'https://dienmaygiakhang.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://dienmaygiakhang.vn';
                                $partner_id = '50';
                                $name = $node->filter('a')->attr('aria-label');
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.amount')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.z-1 span')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                $link_product = $node->filter('a')->attr('href');
                                $linkU = $link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                                }
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }


    }
    public function mmvietnam(){
        try {
            $urlArr = array(
                'https://online.mmvietnam.com/tim-kiem/?cs=hawonkoo',
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.bdl .products-grid .row');
                dd($listItems);
                $categoryUrl = 'https://online.mmvietnam.com';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

//                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
//                        ->where('category_id', $categoryUrl)
//                        ->get()
//                        ->pluck('unique_product_by_date')
//                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://online.mmvietnam.com';
                                $name = $node->filter('.name')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('bdi')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
//                                $node->filter('.text_product_loop_have_km')->each(function ($price) use (&$sale) {
//                                    $sale = $price->text();
//                                });
//                                $link_product = $node->filter('a')->attr('href');
//                                $linkU = $categoryUrl . $link_product;
//                                echo '<pre>';
//                                print_r($linkU);
//                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
//                                if (!in_array($productNameByDate, $existData)) {
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
//                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
//                                    'link_product' => $link,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
//                                }
                            });
//                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function kingshop(){
        try {
            $urlArr = array(
                'https://kingshop.vn/search/result.html?tu_khoa=hawonkoo',
                'https://kingshop.vn/search/result/20.html?tu_khoa=hawonkoo',
                'https://kingshop.vn/search/result.html?tu_khoa=junger',
                'https://kingshop.vn/search/result/20.html?tu_khoa=junger',
                'https://kingshop.vn/search/result.html?tu_khoa=qu%E1%BA%A1t+boss',
                'https://kingshop.vn/search/result.html?tu_khoa=poongsan',
                'https://kingshop.vn/search/result/20.html?tu_khoa=poongsan'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.gBUlRB .kWDRKa');

                $categoryUrl = 'https://kingshop.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://kingshop.vn';
                                $name = $node->filter('h3.fNNzEV')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.hujRwb span')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.gxzNSS')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                    $link_product = $node->filter('a.fBzmKH')->attr('href');
                                $linkU = $link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function bestmua(){
        try {
            $urlArr = array(
                'https://bestmua.vn/search?type=product&q=hawonkoo',
                'https://bestmua.vn/search?type=product&q=hawonkoo&page=2',
                'https://bestmua.vn/search?type=product&q=junger',
                'https://bestmua.vn/search?type=product&q=poongsan',
                'https://bestmua.vn/search?type=product&q=boss'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('#grid_pagination .grid .product_single');
                $categoryUrl = 'https://bestmua.vn';

                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://bestmua.vn';
                                $name = $node->filter('h4')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.product-price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.product-price span')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                echo '<pre>';
                                print_r($sale);
                                echo '</pre>';
                                $link_product = $node->filter('a')->attr('href');
                                $linkU = $categoryUrl . $link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function dienmayquan4(){
        try {
            $urlArr = array(
                'https://dienmayquan4.com/search?query=hawonkoo',
                'https://dienmayquan4.com/search?query=junger',
                'https://dienmayquan4.com/search?query=boss'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.row .product-col');
                $categoryUrl = 'https://dienmayquan4.com';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://dienmayquan4.com';
                                $name = $node->filter('h3 a')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('div.price-box')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $linkU = $categoryUrl . $link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function dienmaytoanlinh(){
        try {
            $urlArr = array(
                'https://dienmaytoanlinh.vn/catalogsearch/result/?q=hawonkoo',
                'https://dienmaytoanlinh.vn/catalogsearch/result/?q=junger',
                'https://dienmaytoanlinh.vn/catalogsearch/result/?q=boss',
                'https://dienmaytoanlinh.vn/catalogsearch/result/?q=poongsan',


            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('ol.product-items.same-height li.product-item');

                $categoryUrl = 'https://dienmaytoanlinh.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://dienmaytoanlinh.vn';
                                $name = $node->filter('.product.details.product-item-details > strong > a')->text();
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }

                                $price = 0;
                                $node->filter('.price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $linkU = $categoryUrl . $link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function dienmayhoanghai(){
        try {
            $urlArr = array(
                'https://dienmayhoanghai.vn/tim-kiem.html?key=hawonkoo',
                'https://dienmayhoanghai.vn/tim-kiem.html?key=junger',
                'https://dienmayhoanghai.vn/tim-kiem.html?key=qu%E1%BA%A1t+boss',
                'https://dienmayhoanghai.vn/tim-kiem.html?key=poongsan'

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.product-list .product-item');
                $categoryUrl = 'https://dienmayhoanghai.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();

                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {
                                $categoryUrl = 'https://dienmayhoanghai.vn';
                                $name = $node->filter('h4')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }

                                $price = 0;
                                $node->filter('.info')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
//                                echo '<pre>';
//                                print_r($sale);
//                                echo '</pre>';
                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function dienmaytayho(){
        try {
            $urlArr = array(
                'https://dienmaytayho.com/search?type=product&q=*hawonkoo*',
                'https://dienmaytayho.com/search?type=product&q=*junger*',
                'https://dienmaytayho.com/search?type=product&q=*boss*'

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.products-view-grid .item-border');
                $categoryUrl = 'https://dienmaytayho.com';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://dienmaytayho.com';
                                $name = $node->filter('h3')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }

                                $price = 0;
                                $node->filter('.price-box')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('.label_product')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$categoryUrl.$link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function aeoneshop(){
        try {
            $urlArr = array(
                'https://aeoneshop.com/search?q=filter=((((titlespace%3Aproduct%20contains%20hawonkoo)%26%26(!(title%3Aproduct**est))%26%26(!(title%3Aproduct**Qu%C3%A0%20t%E1%BA%B7ng)))%7C%7C((sku%3Aproduct%3Dhawonkoo)%26%26(!(sku%3Aproduct%3D05446285))))%26%26(((tag%3Aproduct%3Da%3Aa%3Ahn)%26%26(!(tag%3Aproduct%3Da%3Aa%3Ahcm)))%7C%7C((!(tag%3Aproduct%3Da%3Aa%3Ahn))%26%26(!(tag%3Aproduct%3Da%3Aa%3Ahcm)))%7C%7C((tag%3Aproduct%3Da%3Aa%3Ahn)%26%26(tag%3Aproduct%3Da%3Aa%3Ahcm))))&sortby=(price:product=asc)&type=product&view=hn',
                'https://aeoneshop.com/search?q=filter=((((titlespace%3Aproduct%20contains%20junger)%26%26(!(title%3Aproduct**est))%26%26(!(title%3Aproduct**Qu%C3%A0%20t%E1%BA%B7ng)))%7C%7C((sku%3Aproduct%3Djunger)%26%26(!(sku%3Aproduct%3D05446285))))%26%26(((tag%3Aproduct%3Da%3Aa%3Ahn)%26%26(!(tag%3Aproduct%3Da%3Aa%3Ahcm)))%7C%7C((!(tag%3Aproduct%3Da%3Aa%3Ahn))%26%26(!(tag%3Aproduct%3Da%3Aa%3Ahcm)))%7C%7C((tag%3Aproduct%3Da%3Aa%3Ahn)%26%26(tag%3Aproduct%3Da%3Aa%3Ahcm))))&sortby=(price:product=asc)&type=product&view=hn',

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.product-list .true');
                $categoryUrl = 'https://aeoneshop.com';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://aeoneshop.com';
                                $name = $node->filter('h3')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.pro-price .priceMin')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.product-sale span')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                echo '<pre>';
                                print_r($sale);
                                echo '</pre>';
                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$categoryUrl.$link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function ecomart(){
        try {
            $urlArr = array(
                'https://eco-mart.vn/search?query=hawonkoo&type=product',
                'https://eco-mart.vn/search?query=junger&type=product',
                'https://eco-mart.vn/search?query=boss&type=product'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.products-view-grid .col-6');
                $categoryUrl = 'https://eco-mart.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://eco-mart.vn';
                                $name = $node->filter('.product__box-name')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.product__box-price .price')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.box-promotion')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                echo '<pre>';
                                print_r($sale);
                                echo '</pre>';
                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$categoryUrl.$link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function hahahaha(){
        try {
            $urlArr = array(
                'https://hahahaha.com.vn/tim-kiem-san-pham.html&keyword=hawonkoo',
                'https://hahahaha.com.vn/tim-kiem-san-pham.html&keyword=junger',
                'https://hahahaha.com.vn/tim-kiem-san-pham.html&keyword=boss',
                'https://hahahaha.com.vn/tim-kiem-san-pham.html&keyword=poongsan'
            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('ul.homeproduct li');
                $categoryUrl = 'https://hahahaha.com.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://hahahaha.com.vn/';
                                $name = $node->filter('.pName')->text();

                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }

                                $price = 0;
                                $node->filter('.pos-rela')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);

                                $node->filter('.box-promotion')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$categoryUrl.$link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

    public function bepvuson(){
        try {
            $urlArr = array(
                'https://bepvuson.vn/tim-kiem.html?keyword=hawonkoo&page=1&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=hawonkoo&page=2&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=hawonkoo&page=3&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=hawonkoo&page=4&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=junger&page=1&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=junger&page=2&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=junger&page=3&sort=new_asc&atr=',
                'https://bepvuson.vn/tim-kiem.html?keyword=poongsan'

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('ul.productListVuSon li');

                $categoryUrl = 'https://bepvuson.vn';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://bepvuson.vn';
                                $name = $node->filter('h3')->text();
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                $price = 0;
                                $node->filter('.priceAll')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });
                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                $node->filter('.box-promotion')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$link_product;

                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }
    public function dienmayquanghanh(){
        try {
            $urlArr = array(
                'https://dienmayquanghanh.com/tu-khoa?q=hawonkoo',
                'https://dienmayquanghanh.com/tu-khoa?q=junger',
                'https://dienmayquanghanh.com/tu-khoa?q=qu%E1%BA%A1t+boss',

            );
            foreach ($urlArr as $arr) {
                $totalItem = 0;
                $client = new Client();
                $url = $arr;
                $crawler = $client->request('GET', $url);
                $listItems = $crawler->filter('.grid-load-search .item');
                $categoryUrl = 'https://dienmayquanghanh.com';
                $newProducts = [];
                if (count($listItems) > 0) {
                    $totalItem += count($listItems);

                    $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                        ->where('category_id', $categoryUrl)
                        ->get()
                        ->pluck('unique_product_by_date')
                        ->toArray();
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryUrl, &$existData, &$newProducts) {

                                $categoryUrl = 'https://dienmayquanghanh.com';
                                $name = $node->filter('h3')->text();
                                echo '<pre>';
                                print_r($name);
                                echo '</pre>';
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
                                echo '<pre>';
                                print_r($code_product);
                                echo '</pre>';
                                $price = 0;
                                $node->filter('.price > strong.prPrice')->each(function ($item) use (&$price) {
                                    $price = $item->text();
                                });

                                preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                $price2 = Arr::get($m, 0, 0);
                                $price3 = preg_replace('/\D/', '', $price2);
                                echo '<pre>';
                                print_r($price3);
                                echo '</pre>';
                                $node->filter('.lightning > span')->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });
                                echo '<pre>';
                                print_r($sale);
                                echo '</pre>';
                                $link_product = $node->filter('a')->attr('href');
                                $linkU =$categoryUrl.$link_product;
                                echo '<pre>';
                                print_r($linkU);
                                echo '</pre>';
                                $now = Carbon::now()->format('Y-m-d H:i:s');
                                $productNameByDate = $name . '@@' . $now;
                                $newProducts[] = [
                                    'code_product' => $code_product,
                                    'name' => $name,
                                    'price_partner' => $price3,
                                    'price_sale' => $sale,
                                    'category_id' => $categoryUrl,
                                    'link_product' => $linkU,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                ];
                            });
                        print_r($arr . ' | item: ' . count($listItems) . ', Total Item = ' . $totalItem);
                        ProductPartner::insert($newProducts);
                        DB::commit();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        throw $exception;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
    }

}
