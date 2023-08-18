<?php

namespace App\Console\Commands;

use App\Models\CatalogProductOriginal;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOriginal;
use App\Models\ProductPartner;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class CrawlProductOriginalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:productoriginal';

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
    public function handle()
    {
        $client = new Client();
        $poongSanUrl = 'https://poongsankorea.vn';

        $crawler = $client->request('GET', $poongSanUrl);

        $existData = ProductOriginal::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
            ->where('category_id', $poongSanUrl)
            ->get()
            ->pluck('unique_product_by_date')
            ->toArray();

        $crawler->filter('.product_content')->each(
            function (Crawler $node) use ($poongSanUrl, $existData) {
                $brand_code = 'POONGSAN';
                $name = $node->filter('p.product_name')->text();
                preg_match_all('([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))', $name, $code);;
                $code_product1 = $code[0];
                $code_product = implode(" ", $code_product1);
                $price = $node->filter('.current_price')->text();
                $link_product = $node->filter('p a')->attr('href');
                $link = $poongSanUrl . $link_product;
                $price2 = preg_replace('/\D/', '', $price);
                $productByNameAndDate = $name . '@@' . Carbon::now()->format('Y-m-d');
                if (!in_array($productByNameAndDate, $existData)) {
                    $product = new ProductOriginal;
                    $product->code_product = $code_product;
                    $product->brand = $brand_code;
                    $product->name = $name;
                    $product->price_cost = empty($price2) ? 0 : $price2;
                    $product->price_min = empty($price2) ? 0 : $price2;
                    $product->category_id = $poongSanUrl;
                    $product->link_product = $link;
                    $product->save();
                }
            });

        $client = new Client();
        $arrayBoss = array(
            'quat-dieu-hoa-boss-feab-409-g-50-lit-120w',
            'quat-dieu-hoa-boss-s102-14-lit-100w',
            'quat-dieu-hoa-boss-feab-407-g-35-lit-180w',
            'quat-dieu-hoa-boss-s106-28-lit-160w',
            'quat-dieu-hoa-boss-feab-705-w-35-lit-180w',
            'quat-dieu-hoa-boss-s101-35-lit-180w',
        );
        foreach ($arrayBoss as $arrBoss){
            $quatBossUrl = 'https://quatboss.vn/'.$arrBoss;

            $crawler = $client->request('GET', $quatBossUrl);
            $existData = ProductOriginal::selectRaw("CONCAT(name, '@@', DATE_FORMAT(created_at, '%Y-%m-%d')) as unique_product_by_date")
                ->where('category_id', $quatBossUrl)
                ->get()
                ->pluck('unique_product_by_date')
                ->toArray();

            $crawler->filter('.sticky-sidebar')->each(
                function (Crawler $node) use ($quatBossUrl, $existData) {
                    $url = 'https://quatboss.vn';
                    $brand_code = 'BOSSEL';
                    $node->filter('h1')->each(function ($item) use (&$name) {
                        $name = $item->text();
                    });
                    preg_match_all('([A-Z]+([\-_])?[0-9]+(\+)?([\-A-Za-z]+)?(?!\-\s))', $name, $code);;
                    $code_product1 = $code[0];
                    $code_product = implode(" ", $code_product1);

                    $price = $node->filter('.cprice')->text();
                    $price2 = preg_replace('/\D/', '', $price);

                    $link = $quatBossUrl;

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
