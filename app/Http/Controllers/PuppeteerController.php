<?php

namespace App\Http\Controllers;

use App\Models\ProductOriginal;
use App\Services\PuppeteerService;
use Illuminate\Http\Request;

class PuppeteerController extends Controller
{
    protected $puppeteerService;
    public function __construct(PuppeteerService $puppeteerService)
    {
        $this->puppeteerService = $puppeteerService;
    }
    public function getWebsiteData()
    {
        $puppeteer = new ProductOriginal();
        $browser = $puppeteer->launch((new ProductOriginal())->headless());

        // Truy cập vào trang cần crawl dữ liệu
        $page = $browser->newPage();
        $page->goto('https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo'); // Thay thế URL bằng trang bạn muốn crawl

        // Lấy nội dung trang
        $content = $page->content();
        dd($content);
        // Đóng trình duyệt
        $browser->close();

        // Xử lý dữ liệu đã lấy được
        $data = $this->parseData($content); // Hàm parseData sẽ được bạn viết để trích xuất dữ liệu từ $content

        // Lưu dữ liệu vào database
        ProductOriginal::insert($data); // Thay thế YourModel bằng tên model của bạn

        // Hoặc nếu bạn muốn lưu từng bản ghi một:
        // foreach ($data as $item) {
        //     YourModel::create($item);
        // }

        return 'Crawl and save data successfully!';
    }

    // Viết hàm để parse dữ liệu từ $content (HTML) và trả về mảng dữ liệu
    private function parseData($content)
    {

    }
}
