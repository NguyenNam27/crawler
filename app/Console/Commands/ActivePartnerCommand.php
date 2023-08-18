<?php

namespace App\Console\Commands;

use App\Models\ProductPartner;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\DomCrawler\Crawler;

class ActivePartnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:partner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function getProductCode($string)
    {
        $pattern = [
            '([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',
            '((?!\s)(TMP|BEP|MCP|MPP|MUP)[\-\s]{0,2}\d+)',
        ];
        preg_match_all('/' . implode('|', $pattern) . '/ui', $string, $m);

        return str_replace(' ', '', $m[0][0] ?? $m[1][0] ?? '');
    }
    public function handle()
    {
        //dien may cho lon
        try {
            $apiUrlArr = array(
                'https://dienmaycholon.vn/api/product/result?k=hawonkoo&offset=24',
                'https://dienmaycholon.vn/api/product/result?k=junger&offset=15',
                'https://dienmaycholon.vn/api/product/result?tag[]=boss&k=boss&cid_cate=229&offset=15'
            );
            $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                ->where('category_id', $apiUrlArr)
                ->get()
                ->pluck('unique_product_by_date')
                ->toArray();
            foreach ($apiUrlArr as $Arr) {
                $url = $Arr;

                $jsondata = file_get_contents($url);

                $results = json_decode($jsondata, true)['data'];

                if (!empty($results['hits']['hits'])) {
                    foreach ($results['hits']['hits'] as $result) {
                        $urlD = 'https://dienmaycholon.vn/';

                        $item = $result['_source'];
                        $code_product = $this->getProductCode($item['name']);
                        if ($code_product == "") {
                            continue;
                        }
                        $items[] = [
                            'code' => $code_product,
                            'name' => $item['name'],
                            'discount' => $item['discount'],
                            'cate_alias' => $item['cate_alias'],
                            'alias' => $item['alias'],
                            'urlLinkSP' => 'https://dienmaycholon.vn/' . $item['cate_alias'] . '/' . $item['alias']
                        ];
                        $productByNameAndDate = $item['name'] . '@@' . Carbon::now()->format('Y-m-d');
                        if (!in_array($productByNameAndDate, $existData)) {
                            $data = new ProductPartner();
                            $data->code_product = $code_product;
                            $data->partner_id = '29';
                            $data->name = $item['name'];
                            $data->price_partner = $item['discount'];
                            $data->link_product = 'https://dienmaycholon.vn/' . $item['cate_alias'] . '/' . $item['alias'];
                            $data->category_id = $urlD;
                            $data->save();
                            $existData[] = $productByNameAndDate;
                        }

                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }
        //lazada
        try {
            $urlArr = array(
                'https://www.lazada.vn/tag/hawonkoo/?ajax=true&catalog_redirect_tag=true&isFirstRequest=true&page=1&q=hawonkoo',
                'https://www.lazada.vn/junger-gia-dung-dang-cap/?ajax=true&from=wangpu&isFirstRequest=true&langFlag=en&page=1&pageTypeId=2&q=All-Products',
                'https://www.lazada.vn/poongsan-store/?ajax=true&from=wangpu&isFirstRequest=true&langFlag=en&page=1&pageTypeId=2&q=All-Products',
                'https://www.lazada.vn/boss-flagship-store1621917738/?ajax=true&from=wangpu&isFirstRequest=true&langFlag=en&page=1&pageTypeId=2&q=All-Products',
            );
            $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                ->where('category_id', $urlArr)
                ->get()
                ->pluck('unique_product_by_date')
                ->toArray();
            foreach ($urlArr as $Arr) {
                $url = $Arr;
                $dataL = file_get_contents($url);

                $result = json_decode($dataL, true)['mods'];
                if (!empty($result['listItems'])) {
                    foreach ($result['listItems'] as $dataLazada) {

                        $urlL = 'https://www.lazada.vn/';
                        $code_product2 = $this->getProductCode($dataLazada['name']);
                        if ($code_product2 == "") {
                            continue;
                        }

                        $getdata[] = [
                            'name' => $dataLazada['name'],
                            'code' => $code_product2,
                            'price' => $dataLazada['price'],
                            'itemUrl' => $dataLazada['itemUrl'],
                        ];
                        $value = new ProductPartner();
                        $value->code_product = $code_product2;
                        $value->partner_id = '30';
                        $value->name = $dataLazada['name'];
                        $value->price_partner = $dataLazada['price'];
                        $value->link_product = $dataLazada['itemUrl'];
                        $value->category_id = $urlL;
                        $value->save();
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("crawl data from metamart: {$exception->getMessage()}");
        }

        //Tiki
        try {
            $urlArr = array(
                'https://tiki.vn/api/v2/products?limit=100&include=advertisement&aggregations=2&trackity_id=c571f4dd-3835-c6b0-85ac-c43f49a52c00&q=hawonkoo&_v=filter_revamp',
                'https://tiki.vn/api/v2/products?limit=100&include=advertisement&aggregations=2&trackity_id=c571f4dd-3835-c6b0-85ac-c43f49a52c00&q=junger&_v=filter_revamp'
            );
            $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                ->where('category_id', $urlArr)
                ->get()
                ->pluck('unique_product_by_date')
                ->toArray();
            foreach ($urlArr as $Arr) {
                $url = $Arr;
                $dataL = file_get_contents($url);

                $result = json_decode($dataL, true);
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $dataTiki) {

                        $urlL = 'https://tiki.vn/';
                        $code_product2 = $this->getProductCode($dataTiki['name']);
                        if ($code_product2 == "") {
                            continue;
                        }

                        $getdata[] = [
                            'name' => $dataTiki['name']                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     ,
                            'code' => $code_product2,
                            'price' => $dataTiki['price'],
                            'url'=> $urlL.$dataTiki['url_path']
                        ];
                        $value = new ProductPartner();
                        $value->code_product = $code_product2;
                        $value->partner_id = '31';
                        $value->name = $dataTiki['name'];
                        $value->price_partner = $dataTiki['price'];
                        $value->link_product = $urlL.$dataTiki['url_path'];
                        $value->category_id = $urlL;
                        $value->save();
                    }
                }
            }

        } catch (\Exception $exception) {
            Log::error("crawl data from metamart fail: {$exception->getMessage()}");
        }
        //Pico
        $client = new Client();
        $totalItem = 0;
        $arrayHWK = array(
            'https://pico.vn/brand/hawonkoo',
            'https://pico.vn/brand/junger',
            'https://pico.vn/brand/boss',
        );
        foreach ($arrayHWK as $arrHWK) {
            for ($page = 1; $page < 10; $page++){
                $url = $arrHWK.'?page='.$page;
                $crawler = $client->request('GET', $url);
                $checkpagination = $crawler->filter('#js-prod-list .p-item');
                $totalItem += count($checkpagination);
                if (count($checkpagination) === 0){
                    break;
                }
                $checkpagination->each(
                    function (Crawler $node) use (&$existData) {

                        $url = 'https://pico.vn';
                        $name = $node->filter('.p-text .p-name')->text();
                        $code_product = $this->getProductCode($name);
                        if ($code_product == " ") {
                            return;
                        }
                        $price = $node->filter('.p-price')->text();
                        $price2 = preg_replace('/\D/', '', $price);
                        $priceSale = $node->filter('.p-offer-group')->text();
                        $link_product = $node->filter('a')->attr('href');
                        $link = $url . $link_product;
                        $productByNameAndDate = $name . '@@' . Carbon::now()->format('Y-m-d');
                        if (!in_array($productByNameAndDate, $existData)) {
                            $product = new ProductPartner();
                            $product->code_product = $code_product;
                            $product->partner_id = '32';
                            $product->name = $name;
                            $product->price_partner = empty($price2) ? 0 : $price2;
                            $product->price_sale = $priceSale;
                            $product->category_id = $url;
                            $product->link_product = $link;
                            $product->save();
                            $existData[] = $productByNameAndDate;
                        }
                    });
                //mediaMart
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
                        $mediaMartUrl = 'https://mediamart.vn';

                        $newProducts = [];
                        if (count($listItems) > 0) {
                            $totalItem += count($listItems);
                            $existData = ProductPartner::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                                ->where('category_id', $mediaMartUrl)
                                ->get()
                                ->pluck('unique_product_by_date')
                                ->toArray();
                            try {
                                DB::beginTransaction();
                                $listItems->each(
                                    function (Crawler $node) use ($mediaMartUrl, &$existData, &$newProducts) {
                                        $mediaMartUrl = 'https://mediamart.vn';
                                        $partner_id = '25';
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
                                        $link = $mediaMartUrl . $link_product;

                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;

                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id' => $partner_id,
                                            'name' => $name,
                                            'price_partner' => $price3,
                                            'price_sale' => $sale,
                                            'category_id' => $mediaMartUrl,
                                            'link_product' => $link,
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
                //dien may xanh
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
                                        $mediaMartUrl = 'https://www.dienmayxanh.com';
                                        $partner_id = '28';
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

                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id' => $partner_id,
                                            'name' => $name,
                                            'price_partner' => $price3,
                                            'price_sale' => $sale,
                                            'category_id' => $DMXUrl,
                                            'link_product' => $link,
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
                // sendo
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
                                $value->partner_id = '33';
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
                // Kischenstore
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
                                        $partner_id = '38';
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
                                                'partner_id' => $partner_id,
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
                //khanhvyhome
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
                                        $partner_id = '39';
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
                                            'partner_id' => $partner_id,
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
                //bepvuson
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
                                        $partner_id = '43';
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
                                        $linkU = $link_product;

                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id' => $partner_id,
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
                //hahahaha
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
                                        $partner_id = '40';
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
                                            'partner_id'=>$partner_id,
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
                //dienmayhoanghai
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
                                        $partner_id = '37';
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

                                        $link_product = $node->filter('a')->attr('href');
                                        $linkU =$link_product;

                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id'=>$partner_id,
                                            'name' => $name,
                                            'price_partner' => $price3,
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
                //điện máy tây hồ
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
                                        $partner_id = '41';
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
                                            'partner_id'=>$partner_id,
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
                //dien may toan linh
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
                                        $partner_id = '42';
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
                                            'partner_id'=>$partner_id,
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
                //aeonshop
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
                                        $partner_id = '47';
                                        $name = $node->filter('h3')->text();
                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }
                                        $price = 0;
                                        $node->filter('.pro-price .priceMin')->each(function ($item) use (&$price) {
                                            $price = $item->text();
                                        });
                                        preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                        $price2 = Arr::get($m, 0, 0);
                                        $price3 = preg_replace('/\D/', '', $price2);
                                        $node->filter('.product-sale span')->each(function ($price) use (&$sale) {
                                            $sale = $price->text();
                                        });
                                        $link_product = $node->filter('a')->attr('href');
                                        $linkU =$categoryUrl.$link_product;
                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id'=>$partner_id,
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
                //ecoMart
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
                                        $partner_id = '44';
                                        $name = $node->filter('.product__box-name')->text();

                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }

                                        $price = 0;
                                        $node->filter('.product__box-price .price')->each(function ($item) use (&$price) {
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
                                            'partner_id'=>$partner_id,
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
                // dien may quang hanh
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
                                        $partner_id = '46';
                                        $name = $node->filter('h3')->text();
                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }

                                        $price = 0;
                                        $node->filter('.price > strong.prPrice')->each(function ($item) use (&$price) {
                                            $price = $item->text();
                                        });

                                        preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                        $price2 = Arr::get($m, 0, 0);
                                        $price3 = preg_replace('/\D/', '', $price2);

                                        $node->filter('.lightning > span')->each(function ($price) use (&$sale) {
                                            $sale = $price->text();
                                        });

                                        $link_product = $node->filter('a')->attr('href');
                                        $linkU =$categoryUrl.$link_product;
                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id'=>$partner_id,
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
                //bestmua
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
                                        $partner_id = '35';
                                        $categoryUrl = 'https://bestmua.vn';
                                        $name = $node->filter('h4')->text();

                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }

                                        $price = 0;
                                        $node->filter('.product-price')->each(function ($item) use (&$price) {
                                            $price = $item->text();
                                        });
                                        preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                        $price2 = Arr::get($m, 0, 0);
                                        $price3 = preg_replace('/\D/', '', $price2);

                                        $node->filter('.product-price span')->each(function ($price) use (&$sale) {
                                            $sale = $price->text();
                                        });

                                        $link_product = $node->filter('a')->attr('href');
                                        $linkU = $categoryUrl . $link_product;

                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id'=>$partner_id,
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
                // điện máy quận 4
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
                                        $partner_id = '48';
                                        $name = $node->filter('h3 a')->text();

                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }

                                        $price = 0;
                                        $node->filter('div.price-box')->each(function ($item) use (&$price) {
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
                                            'partner_id' => $partner_id,
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
                //kingshop
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
                                        $partner_id='36';
                                        $categoryUrl = 'https://kingshop.vn';
                                        $name = $node->filter('h3.fNNzEV')->text();

                                        $code_product = $this->getProductCode($name);
                                        if ($code_product == "") {
                                            return;
                                        }

                                        $price = 0;
                                        $node->filter('.hujRwb span')->each(function ($item) use (&$price) {
                                            $price = $item->text();
                                        });
                                        preg_match('/([0-9\.,]+)\s?\w+/', $price, $m);
                                        $price2 = Arr::get($m, 0, 0);
                                        $price3 = preg_replace('/\D/', '', $price2);

                                        $node->filter('.gxzNSS')->each(function ($price) use (&$sale) {
                                            $sale = $price->text();
                                        });
                                        $link_product = $node->filter('a.fBzmKH')->attr('href');
                                        $linkU = $link_product;

                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        $newProducts[] = [
                                            'code_product' => $code_product,
                                            'partner_id' => $partner_id,
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
                //điện máy gia khang

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

                                        $node->filter('.z-1 span')->each(function ($price) use (&$sale) {
                                            $sale = $price->text();
                                        });
                                        $link_product = $node->filter('a')->attr('href');
                                        $linkU = $link_product;
                                        $now = Carbon::now()->format('Y-m-d H:i:s');
                                        $productNameByDate = $name . '@@' . $now;
                                        if (!in_array($productNameByDate, $existData)) {
                                            $newProducts[] = [
                                                'code_product' => $code_product,
                                                'partner_id'=>$partner_id,
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
    }
}
