<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection): void
    {
        // This class is used with Excel::toArray() in validateExcelData(),
        // which does not call this method. The WithHeadingRow concern handles
        // header row normalization for toArray().
    }
}
