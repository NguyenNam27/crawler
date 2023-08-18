<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $tableData;
    public $cate;
    public $categoryCountsTemp;
    public $excelFile;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(    $tableData,$cate,$categoryCountsTemp,$excelFile)
    {
        $this->tableData = $tableData;
        $this->cate  = $cate;
        $this->categoryCountsTemp = $categoryCountsTemp;
        $this->excelFile = $excelFile;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
//        $attachmentPath = storage_path('app/public/ '.$this->excelFile);
        return $this->subject('Sản phẩm có sự thay đổi về giá')
            ->view('email.mail')
            ->with(['tableData'=>$this->tableData],['cate'=>$this->cate],['categoryCountsTemp'=>$this->categoryCountsTemp]);
//            ->attach($attachmentPath);

    }
}
