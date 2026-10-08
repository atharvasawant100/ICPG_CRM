<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExportTemplate implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            [
                'John Doe',
                'john@example.com',
                '1234567890',
                'Example Corp',
                'web',
                'new',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'name',
            'email',
            'phone',
            'company',
            'source',
            'status',
        ];
    }
}
