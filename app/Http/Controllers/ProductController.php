<?php

namespace App\Http\Controllers;

use App\Exports\DataSendMailExport;
use App\Exports\HistoryProductExport;
use App\Mail\SendMail;
use App\Models\Brand;
use App\Models\Email;
use App\Models\Partner;
use App\Models\ProductOriginal;
use App\Models\ProductPartner;
use App\Models\ProductReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\ResultProductExport;
use Illuminate\Support\Facades\Redirect;
                                            use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Session;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function resultProduct(Request $request)
    {
        return redirect()->route('report');

        $search = $request->input('search');

        $originalSite = $request->input('mySiteOption', '');

        $partnerSite = $request->input('partnerSiteOption', '');

        $now = Carbon::now()->format('Y-m-d');

        $cate = ProductOriginal::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');

        $catePartner = ProductPartner::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');

        $products = DB::table('product_originals')
            ->selectRaw("
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.price_sale AS partner_priceSale,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->when(!empty($originalSite), function ($query) use ($originalSite) {
                $query->where('product_originals.category_id', $originalSite);
            })
            ->when(!empty($partnerSite), function ($query) use ($partnerSite) {
                $query->where('product_partners.category_id', $partnerSite);
            })
            ->whereDate('product_partners.created_at', Carbon::now())
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->distinct()
            ->paginate(25);
        $countTotal = DB::table(DB::raw('(
            SELECT DISTINCT
            product_partners.created_at AS date,
            product_originals.category_id AS cate_pr_or,
            product_originals.code_product,
            product_originals.`name` AS nam_pr_or,
            product_originals.price_cost AS original_price,
            product_originals.price_min AS original_priceMin,
            product_partners.price_partner AS partner_price,
            product_partners.price_sale AS partner_priceSale,
            product_partners.link_product AS link_pr_cus,
            product_partners.price_partner - product_originals.price_cost AS price_difference,
            product_partners.price_partner - product_originals.price_min AS priceMin_difference
        FROM
            product_originals
        LEFT JOIN
            product_partners ON product_partners.code_product = product_originals.code_product
        WHERE
             product_partners.created_at BETWEEN "'.$now.' 00:00:00" AND "'.$now.' 23:59:59"
        ) AS number_record'))
            ->selectRaw('COUNT(*) as record_count')
            ->value('record_count');
        $filteredData  = DB::table('product_originals')
            ->selectRaw("
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.price_sale AS partner_priceSale,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->where('product_originals.category_id', $originalSite)
            ->where('product_partners.category_id', $partnerSite)
            ->whereDate('product_partners.created_at', Carbon::now())
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->distinct()
            ->paginate(25);
            return view('product.list', [
            'cate' => $cate,
            'catePartner' => $catePartner,
            'products' => $products,
            'originalSite' => $originalSite,
            'search' => $search,
            'countTotal'=>$countTotal,
                'filteredData'=>$filteredData
        ]);

    }
    public function historyComparePrices(Request $request)
    {
        $search = $request->input('search');

        $originalSite = $request->input('mySiteOption', '');

        $partnerSite = $request->input('partnerSiteOption', '');

        $startDate = $request->input('start_date', Carbon::now());

        $cate = ProductOriginal::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $catePartner = ProductPartner::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $products = DB::table('product_originals')->selectRaw("
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.price_sale AS partner_priceSale,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
        )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->when(!empty($originalSite), function ($query) use ($originalSite) {
                $query->where('product_originals.category_id', $originalSite);
            })
            ->when(!empty($partnerSite), function ($query) use ($partnerSite) {
                $query->where('product_partners.category_id', $partnerSite);
            })
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->when(!empty($startDate), function ($query) use ($startDate) {
                $query->whereDate('product_partners.created_at', $startDate);
            })
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->distinct()
            ->paginate(25);

        return view('history.list', [
            'cate' => $cate,
            'products' => $products,
            'originalSite' => $originalSite,
            'catePartner' => $catePartner,
            'startDate' => $startDate,
        ]);
    }

    public function exportResultProduct(Request $request)
    {
        $search = $request->input('search');
        $originalSite = $request->input('mySiteOption', '');

        return Excel::download(new ResultProductExport($search, $originalSite), date("Ymd") .'_result.xlsx');
    }

    public function exportHistoryProduct(Request $request)
    {
        $search = $request->input('search');
        $originalSite = $request->input('mySiteOption', '');
        return Excel::download(new HistoryProductExport($search, $originalSite), 'history.xlsx');
    }

    public function listEmail()
    {
        $listMail = DB::table('emails')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('email.list', [
            'listMail' => $listMail
        ]);
    }

    public function addMail()
    {
        return view('email.create');
    }

    public function saveMail(Request $request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ];
        Email::create($data);
        Session::put('message', 'Thêm Email thành công');
        return Redirect::to('list-email');
    }

    public function editEmail($id)
    {
        $editMail = DB::table('emails')->where('id', $id)->first();
        return view('email.edit', [
            'editMail' => $editMail
        ]);
    }

    public function updateMail(Request $request, $id)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ];
        DB::table('emails')->where('id', $id)->update($data);
        Session::put('message', 'Cập nhập Email thành công');
        return Redirect::to('list-email');
    }

    public function deleteMail($id)
    {
        DB::table('emails')->where('id', $id)->delete();
        Session::put('message', 'Xóa đối tác thành công');
        return Redirect::to('list-email');
    }

    public function sendNotification(Request $request, $id)
    {
        $now = Carbon::now()->format('Y-m-d');

        $email = DB::table('emails')->where('id', $id)->first();

        if (!$email) {
            return response('Email is not exist', 400);
        }
        $cate = ProductOriginal::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $categoryCountsTemp = [];
        foreach ($cate as $itemC){
            $count = DB::table(DB::raw('(
            SELECT DISTINCT
            product_partners.created_at AS date,
            product_originals.category_id AS cate_pr_or,
            product_originals.code_product,
            product_originals.`name` AS nam_pr_or,
            product_originals.price_cost AS original_price,
            product_originals.price_min AS original_priceMin,
            product_partners.price_partner AS partner_price,
            product_partners.price_sale AS partner_priceSale,
            product_partners.link_product AS link_pr_cus,
            product_partners.price_partner - product_originals.price_cost AS price_difference,
            product_partners.price_partner - product_originals.price_min AS priceMin_difference
        FROM
            product_originals
        LEFT JOIN
            product_partners ON product_partners.code_product = product_originals.code_product
        WHERE
            product_originals.category_id = "'.$itemC.'"
            AND product_partners.created_at BETWEEN "'.$now.' 00:00:00" AND "'.$now.' 23:59:59"
        ) AS number_record'))
                ->selectRaw('COUNT(*) as record_count')
                ->value('record_count');
            $categoryCountsTemp[$itemC] = $count;
        }
        $excelFileName = 'table_data.xlsx';
        $tableData = DB::table('product_originals')
            ->selectRaw("
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.price_sale AS partner_priceSale,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->whereDate('product_partners.created_at', Carbon::now())
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->distinct()
            ->get();
        Mail::to($email)->send(new SendMail($tableData,$cate, $categoryCountsTemp,$excelFileName));
        Session::put('message', 'Kết quả đã được gửi tới Email thành công');
        return redirect('/list-email');
    }

    public function reportProduct(Request $request)
    {
        $search = $request->input('search');

        $originalSite = $request->input('mySiteOption', '');
        $brand = $request->input('brand', '');

        $partner_id = $request->input('partner_id', '');

        $startDate = $request->input('start_date', Carbon::now());
        $now = Carbon::now()->format('Y-m-d');

        $cate = Brand::select('name')
            ->get()
            ->pluck('name');

        $catePartner = ProductPartner::select('category_id')
            ->groupBy('category_id')
            ->get()
            ->pluck('category_id');
        $partners = Partner::where(['status' => 1])->get();
        $brands = Brand::select('*')->get();

        $products = ProductReport::selectRaw("
                product_partners.created_at AS date,
				product_originals.brand AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->when(!empty($originalSite), function ($query) use ($originalSite) {
                $query->where('product_originals.category_id', $originalSite);
            })
            ->when(!empty($brand), function ($query) use ($brand) {
                $query->where('product_originals.brand', $brand);
            })
            ->when(!empty($partner_id), function ($query) use ($partner_id) {
                $query->where('product_partners.partner_id', $partner_id);
            })
            ->whereDate('product_partners.created_at', Carbon::now())
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->groupBy(['product_originals.code_product', 'product_originals.brand'])
            ->distinct()
            ->paginate(25);
        $countTotal = DB::table(DB::raw('(
            SELECT DISTINCT
            product_partners.created_at AS date,
            product_originals.category_id AS cate_pr_or,
            product_originals.code_product,
            product_originals.`name` AS nam_pr_or,
            product_originals.price_cost AS original_price,
            product_originals.price_min AS original_priceMin,
            product_partners.price_partner AS partner_price,
            product_partners.price_sale AS partner_priceSale,
            product_partners.link_product AS link_pr_cus,
            product_partners.price_partner - product_originals.price_cost AS price_difference,
            product_partners.price_partner - product_originals.price_min AS priceMin_difference
        FROM
            product_originals
        LEFT JOIN
            product_partners ON product_partners.code_product = product_originals.code_product
        WHERE
             product_partners.created_at BETWEEN "'.$now.' 00:00:00" AND "'.$now.' 23:59:59"
        ) AS number_record'))
            ->selectRaw('COUNT(*) as record_count')
            ->value('record_count');
        $filteredData  = DB::table('product_originals')
            ->selectRaw("
                product_partners.created_at AS date,
				product_originals.category_id AS cate_pr_or,
                product_originals.code_product,
				product_originals.`name` as nam_pr_or,
                product_originals.price_cost AS original_price,
                product_originals.price_min AS original_priceMin,
                product_originals.link_product AS link_product_pr_or,
                product_partners.price_partner AS partner_price,
                product_partners.price_sale AS partner_priceSale,
                product_partners.link_product AS link_pr_cus,
                product_partners.price_partner - product_originals.price_cost AS price_difference,
                product_partners.price_partner - product_originals.price_min AS priceMin_difference"
            )
            ->leftJoin('product_partners', 'product_partners.code_product', '=', 'product_originals.code_product')
            ->where('product_originals.name', 'like', '%' . $search . '%')
            ->where('product_partners.price_partner', '!=', 'product_originals.price_cost')
            ->where('product_originals.category_id', $originalSite)
            ->whereDate('product_partners.created_at', Carbon::now())
            ->when(!empty($startDate), function ($query) use ($startDate) {
                $query->whereDate('product_partners.created_at', $startDate);
            })
            ->orderBy('product_partners.created_at', 'DESC')
            ->orderBy('product_originals.code_product', 'ASC')
            ->groupBy(['product_originals.code_product', 'product_originals.brand'])
            ->distinct()
            ->paginate(25);
        return view('report.list', [
            'cate' => $cate,
            'catePartner' => $catePartner,
            'products' => $products,
            'originalSite' => $originalSite,
            'brand' => $brand,
            'brands' => $brands,
            'search' => $search,
            'countTotal'=>$countTotal,
            'filteredData'=>$filteredData,
            'partners'=>$partners,
            'partner_id'=>$partner_id,
            'startDate' => $startDate,
        ]);
    }
}
