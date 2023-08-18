<?php

namespace App\Listeners;

use App\Events\AddPartner;
use App\Models\Partner;
use App\Models\ProductPartner;
use Carbon\Carbon;
use Composer\DependencyResolver\Request;
use Goutte\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\DomCrawler\Crawler;

class NotifyPartner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {

    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AddPartner  $event
     * @return void
     */
    public function handle(AddPartner $event,$id)
    {
        $partner = $event->partner;

        $partner2 = DB::table('partners')->where('id', $id)->first();
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
            return redirect('/list-partner')    ;
        }
        $count = 0;
        try {
            foreach ($keywords as $keyword):
                $client = new Client();
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
                    try {
                        DB::beginTransaction();
                        $listItems->each(
                            function (Crawler $node) use ($categoryId, $existData, &$newProductsPartner, $requestArr) {
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

                                $node->filter($requestArr['class_sale'])->each(function ($price) use (&$sale) {
                                    $sale = $price->text();
                                });

                                $link_product = $node->filter($requestArr['class_link'])->attr('href');
                                $link = $categoryId . $link_product;
                                $now = Carbon::now()->format('Y-m-d');
                                $productNameByDate = $name . '@@' . $now;
                                if (!in_array($productNameByDate, $existData)) {
                                    $newProductsPartner[] = [
                                        'code_product' => $code_product,
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
        Session::put('message', 'CRAWL DATA SUCCESSFULLY {' . $count . ' SP}');
        return redirect('/list-partner');
    }
}
