<?php

namespace App\Exports;


use App\Models\{StatisticsModel};
use Maatwebsite\Excel\Concerns\{FromCollection,WithHeadings,WithMapping,WithStyles,WithTitle};
use Maatwebsite\Excel\Concerns\{WithCustomStartCell,WithEvents,ShouldAutoSize};
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatisticsExportSheet1 implements FromCollection,WithHeadings,WithMapping,WithCustomStartCell,WithEvents,ShouldAutoSize,WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $statistics_data = StatisticsModel::all();
        return $statistics_data;
    }

    public function headings() : array {
        return [
            'Month of Lookup',
            'Year of Lookup',
            'Application Lookups',
            'VIN Lookups',
            'Keyword Lookups'
        ];
    }

    public function map($statistics_data): array
    {
        return [
            $statistics_data->app_month,
            $statistics_data->app_year,
            $statistics_data->application_lookups,
            $statistics_data->vin_lookups,
            $statistics_data->keyword_lookups,
        ];

    }


    public function startCell(): string
    {
        return 'A2';
    }


    public function title(): string
    {
        return 'Lookup Summary';
    }


    public function registerEvents(): array {

        return [
            AfterSheet::class => function(AfterSheet $event) {
                /** @var Sheet $sheet */
                $sheet = $event->sheet;
                

                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', "Lookup Summary By Month");


                //for 1st row
                $sheet->getDelegate()->getStyle('A1:F1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('4682b4');

                $sheet->getDelegate()->getStyle('A1:F1')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(15)
                        ->getColor()
                        ->setARGB('FFFFFF');

                $sheet->getDelegate()->getStyle('A1:F1')
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


                //for 2nd row
                $sheet->getDelegate()->getStyle('A2:F2')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('4682b4');

                $sheet->getDelegate()->getStyle('A2:F2')
                        ->getFont()
                        ->setBold(true)
                        ->getColor()
                        ->setARGB('FFFFFF');

                $sheet->getDelegate()->getStyle('A2:F2')
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            },
        ];
    }
}
