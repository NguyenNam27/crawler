<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class HistoryProductExport implements FromCollection
{
    protected $search;
    protected $originalSite;

    public function __construct($search, $originalSite)
    {
        $this->search = $search;
        $this->originalSite = $originalSite;
    }

    public function headings(): array
    {
        return [
            '#',
            'Ngày',
            'Danh Mục',
            'Mã Sản Phẩm BTP',
            'Tên Sản Phẩm BTP',
            'Giá Sản Phẩm BTP',
            'Giá Sản Phẩm BTP Min',
            'Link Sản Phẩm BTP',
            'Giá SP Đối Tác',
            'Link SP Đối Tác',
            'Giá chênh lệch',
            'Giá chênh lệch min',
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DB::table('product_originals')

            ->selectRaw("
                product_originals.id,
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $this->search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->when(!empty($this->originalSite), function ($query) {
                $query->where('product_originals.category_id', $this->originalSite);
            })
            ->get();
    }
}
