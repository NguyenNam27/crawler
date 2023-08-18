<?php

namespace Tests\Browser;

use App\Models\ProductPartner;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class duskSpiderTest extends DuskTestCase
{
    protected static $domain = 'www.nguyenkim.com';
    protected static $startUrl = 'https://www.nguyenkim.com/tim-kiem.html?tu-khoa=hawonkoo';
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }
//    public function setUp(): void{
//        parent::setUp();
//        $this->artisan('migrate:fresh');
//    }
    public function urlSpider()
    {

        $startingLink = ProductPartner::create([
            'url' => self::$startUrl,
            'isCrawled' => false,
        ]);
        var_dump($startingLink);
        $this->browse(function (Browser $browser) use ($startingLink) {
            $this->getLinks($browser, $startingLink);
        });
    }
    protected function getLinks(Browser $browser, $currentUrl){

        $this->processCurrentUrl($browser, $currentUrl);


        try{

            foreach(ProductPartner::where('isCrawled', false)->get() as $link) {
                $this->getLinks($browser, $link);
            }


        }catch(\Exception $e){

        }
    }
    protected function processCurrentUrl(Browser $browser, $currentUrl){

        //Check if already crawled
        if(ProductPartner::where('url', $currentUrl->url)->first()->isCrawled == true)
            return;

        //Visit URL
        $browser->visit($currentUrl->url);

        //Get Links and Save to DB if Valid
        $linkElements = $browser->driver->findElements(WebDriverBy::tagName('a'));
        foreach($linkElements as $element){
            $href = $element->getAttribute('href');
            $href = $this->trimUrl($href);
            if($this->isValidUrl($href)){
                //var_dump($href);
                ProductPartner::create([
                    'url' => $href,
                    'isCrawled' => false,
                ]);
            }
        }

        //Update current url status to crawled
        $currentUrl->isCrawled = true;
        $currentUrl->status  = $this->getHttpStatus($currentUrl->url);
        $currentUrl->title = $browser->driver->getTitle();
        $currentUrl->save();
    }
    protected function isValidUrl($url){
        $parsed_url = parse_url($url);

        if(isset($parsed_url['host'])){
            if(strpos($parsed_url['host'], self::$domain) !== false && !ProductPartner::where('url', $url)->exists()){
                return true;
            }
        }
        return false;
    }
    protected function trimUrl($url){
        $url = strtok($url, '#');
        $url = rtrim($url,"/");
        return $url;
    }

    protected function getHttpStatus($url){
        $headers = get_headers($url, 1);
        return intval(substr($headers[0], 9, 3));
    }
}
