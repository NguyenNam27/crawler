<?php

namespace App\Services;
use Nesk\Puphpeteer\Puppeteer;

class PuppeteerService
{
    protected $puppeteer;
    public function __construct()
    {
        $this->puppeteer = new Puppeteer;
    }
    public function getWebsiteData($url)
    {
        $browser = $this->puppeteer->launch();
        $page = $browser->newPage();
        $a = $page->goto('https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo');
        // Lấy dữ liệu từ trang web
        $data = $page->evaluate(function () {
        });

        $browser->close();

        return $data;
    }
}
