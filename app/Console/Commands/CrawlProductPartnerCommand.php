<?php

namespace App\Console\Commands;

use App\Models\ProductPartner;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CrawlProductPartnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:productpartner';

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
                                $code_product = $this->getProductCode($name);
                                if ($code_product == "") {
                                    return;
                                }
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
