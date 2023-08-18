<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\Partner;
use App\Models\ProductReport;
use App\Models\ProductReportExcel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResultProductExport implements FromCollection, WithHeadings
{

    protected $search;
    protected $originalSite;

    public $brands, $partners;

    public function __construct($search, $originalSite)
    {
        $this->search = $search;
        $this->originalSite = $originalSite;
        $this->partners = Partner::where(['status' => 1])->get();
        $this->brands = Brand::select('*')->get();
    }

    public function headings(): array
    {
        $headers = [
            'Brand',
            'Mã SP',
            'Giá NY',
            'Giá Min',
        ];
        foreach ($this->partners as $partner){
            $headers[] = $partner->name;
        }
        return $headers;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $products = ProductReportExcel::selectRaw("
				product_originals.brand,
                product_originals.code_product,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin
               "
        )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
//            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->whereDate('product_partners.created_at', Carbon::now())
            ->orderBy('product_partners.created_at', 'DESC')
            ->groupBy(['product_originals.code_product', 'product_originals.brand'])
            ->get();

        foreach ($products as $product){
            $prices = $product->getProductPartner()->toArray();
            foreach ($this->partners as $partner){
                $pp = "partner_{$partner->id}";
                $pr = '';
                if(!empty($prices)){
                    $exits = 0;
                    foreach ($prices as $k=> $price){
                        if($price['partner_id'] == $partner->id){
//                            echo '<a href="'.$price['link_product'].'" target="_blank">';
                            $pr = ($price['price_partner']);
                            $diff = ($price['price_partner'] - $product->original_price)/1000;
                            $diffMin = ($price['price_partner'] - $product->original_priceMin)/1000;

                            $diffPrice = [];

                            if($diff < 0){
                                array_push($diffPrice, number_format($diff) . 'k');
                            }else{
                                array_push($diffPrice, '+' . number_format($diff) . 'k');
                            }

                            if($diffMin != $diff){
                                if($diff < 0){
                                    array_push($diffPrice, number_format($diffMin) . 'k');
                                }else{
                                    array_push($diffPrice, '+' . number_format($diffMin) . 'k');
                                }
                            }

//                            echo '<p style="margin:0;white-space: nowrap"><i style="color:red">' . implode('</i>|<i style="color:red">', $diffPrice) . '</i></p>';

//                            echo '</a>';
                            if($price['price_sale'] != ""){
//                                                                echo '<p class="line-clamp l1 m0" title="' . $price['price_sale'] . '">' . $price['price_sale'] . '</p>';
                            }
                            unset($prices[$k]);
                            $exits++;
                        }
                    }

                    if($exits == 0){
                        $pr = '-';
                    }
                }else{
                    $pr = '-';
                }

                $product->{$pp} = $pr;
            }
        }

        return $products;
    }
}
