<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class DataSendMailExport implements FromCollection
{
    protected $tableData;

    public function __construct($tableData)
    {
        $this->tableData = $tableData;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->tableData);

    }
}
