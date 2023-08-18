<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\ProductPartner;
use App\Models\ProductOriginal;
use App\Models\Product;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CrawlCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Craw data';

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
    public function handle()
    {
        try {
            $client = new Client();
            $array = array('bep', 'may-rua-bat', 'may-hut-mui', 'dung-cu-nha-bep', 'lo-vi-song');
            $jungerUrl = 'https://junger.vn/';


            $existData = ProductOriginal::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                ->where('category_id', $jungerUrl)
                ->get()
                ->pluck('unique_product_by_date')
                ->toArray();
//            $jungerUrlP = 'https://junger.vn/bep?p=1';
//            $crawler = $client->request('GET', $jungerUrlP);
//            $lastPage = $crawler->filter('.pagination-md li');

//            $lastPage = $lastPage->getNode($lastPage->count()-2)->textContent;
                foreach ($array as $arr) {
                for ($page = 1; $page <= 99; $page++) {
                        $url = 'https://junger.vn/' . $arr . '?p=' . $page;

                    $crawler = $client->request('GET', $url);
                    $checkItems = $crawler->filter('.item-wrapper');
                    if (count($checkItems) === 0) {
                        break;
                    }
                    $checkItems->each(
                        function (Crawler $node) use ($jungerUrl, &$existData) {
                            $jungerUrl = 'https://junger.vn';
                            $brand_code = 'JUNGER';
                            $name = $node->filter('.item-name')->text();
                            preg_match_all('([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',$name , $code);;
                            $code_product1 = $code[0];
                            $code_product = implode(" ",$code_product1);
                            $price = $node->filter('.price_box')->text();
                            $price2 = preg_replace('/\D/', '', $price);
                            $link_product = $node->filter('.primary-img')->attr('href');
                            $link = $jungerUrl . $link_product;

                            $productByNameAndDate = $name . '@@' . Carbon::now()->format('Y-m-d');
                            if (!in_array($productByNameAndDate, $existData)) {
                                $product = new ProductOriginal;
                                $product->code_product = $code_product;
                                $product->brand = $brand_code;
                                $product->name = $name;
                                $product->price_cost = empty($price2) ? 0 : $price2;
                                $product->price_min = empty($price2) ? 0 : $price2;
                                $product->category_id = $jungerUrl;
                                $product->link_product = $link;
                                $product->save();
                                $existData[] = $productByNameAndDate;
                            }
                        });
                    print_r($arr . ', page: ' . $page . ' \n');
                }

            }
        } catch (\Exception $exception) {
            dd($exception);
        }

        $client = new Client();
        $totalItem = 0;
        $arrayHWK = array(
            'bep-tu',
            'noi-chien-khong-dau',
            'may-ep-cham',
            'quat-cay',
            'noi-com-dien',
            'noi-ap-suat',
            'am-sieu-toc',
            'may-tiet-trung',
            'noi-lau-dien',
            'noi-lau-nuong-da-nang',
            'may-lam-sua-hat',
            'may-say-toc',
            'may-vat-cam',
            'vot-muoi',
            'dung-cu-nha-bep',
        );

        $hawonkooUrl = 'https://hawonkoo.vn';
        $existData = ProductOriginal::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
            ->where('category_id', $hawonkooUrl)
            ->get()
            ->pluck('unique_product_by_date')
            ->toArray();

        foreach ($arrayHWK as $arrHWK) {
            for ($page = 1; $page < 10; $page++){
                $url = 'https://hawonkoo.vn/'.$arrHWK.'?p='.$page;

                $crawler = $client->request('GET', $url);
                $checkpagination = $crawler->filter('.product_content');
                $totalItem += count($checkpagination);
                if (count($checkpagination) === 0){
                    break;
                }
                $checkpagination->each(
                    function (Crawler $node) use (&$existData) {
                        $url = 'https://hawonkoo.vn';
                        $brand_code = 'HAWONKOO';
                        $name = $node->filter('a h3.product_name')->text();
                        preg_match_all('([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',$name , $code);
                        $code_product1 = $code[0];
                        $code_product = implode(" ",$code_product1);
                        $price = $node->filter('.current_price')->text();
                        $link_product = $node->filter('a')->attr('href');
                        $link = $url . $link_product;
                        $price2 = preg_replace('/\D/', '', $price);

                        $productByNameAndDate = $name . '@@' . Carbon::now()->format('Y-m-d');
                        if (!in_array($productByNameAndDate, $existData)) {
                            $product = new ProductOriginal;
                            $product->code_product = $code_product;
                            $product->brand = $brand_code;
                            $product->name = $name;
                            $product->price_cost = empty($price2) ? 0 : $price2;
                            $product->price_min = empty($price2) ? 0 : $price2;
                            $product->category_id = $url;
                            $product->link_product = $link;
                            $product->save();
                            $existData[] = $productByNameAndDate;
                        }

                    });
            }
        }
        $totalItem = 0;
        $client = new Client();
        $arrayBoss = array('ghe-massage-toan-than','may-chay-bo','xe-dap-tap','dung-cu-massage', 'ghe-massage-boss');
        $bossmassageUrl = 'https://bossmassage.vn';
        $existData = ProductOriginal::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
            ->where('category_id', $bossmassageUrl)
            ->get()
            ->pluck('unique_product_by_date')
            ->toArray();

        foreach ($arrayBoss as $arrBoss){
            $url = 'https://bossmassage.vn/'.$arrBoss;

            $crawler = $client->request('GET', $url);
            $listItem = $crawler->filter('.single_product');
            if(count($listItem) > 0){
                $totalItem += count($listItem);
                $listItem->each(
                    function (Crawler $node) use ($existData) {
                        $url = 'https://bossmassage.vn';
                        $brand_code = 'BOSSMS';
                        $name = $node->filter('h3.product_name a')->text();
                        preg_match_all('([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))',$name , $code);;
                        $code_product1 = $code[0];
                        $code_product = implode(" ",$code_product1);
                        $price = $node->filter('.current_price')->text();
                        $link_product = $node->filter('a.primary_img')->attr('href');
                        $link = $link_product;
                        $price2 = preg_replace('/\D/', '', $price);

                        $productByNameAndDate = $name . '@@' . Carbon::now()->format('Y-m-d');
                        if (!in_array($productByNameAndDate, $existData)) {
                            $product = new ProductOriginal;
                            $product->code_product = $code_product;
                            $product->brand = $brand_code;
                            $product->name = $name;
                            $product->price_cost = empty($price2) ? 0 : $price2;
                            $product->price_min = empty($price2) ? 0 : $price2;
                            $product->category_id = $url;
                            $product->link_product = $link;
                            $product->save();
                        }
                    });
            }
        }
    }
}
