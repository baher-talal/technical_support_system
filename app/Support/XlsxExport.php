<?php
namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class XlsxExport
{
    /** يرسل ملف Excel للتحميل. $rows مصفوفة صفوف، كل صف مصفوفة قيم نصية بنفس ترتيب $headers */
    public static function download(string $file, array $headers, iterable $rows, string $title = 'Sheet1')
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle($title)->setRightToLeft(true);
        $cols = count($headers);
        foreach (array_values($headers) as $i => $h) $sh->setCellValueExplicit(Coordinate::stringFromColumnIndex($i + 1) . '1', (string) $h, DataType::TYPE_STRING);
        $sh->getStyle('A1:' . Coordinate::stringFromColumnIndex($cols) . '1')->getFont()->setBold(true);
        $n = 1;
        foreach ($rows as $row) {
            $n++;
            foreach (array_values($row) as $c => $v) $sh->setCellValueExplicit(Coordinate::stringFromColumnIndex($c + 1) . $n, (string) ($v ?? ''), DataType::TYPE_STRING);
        }
        for ($c = 1; $c <= $cols; $c++) $sh->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        $sh->freezePane('A2');

        return response()->streamDownload(fn () => (new Xlsx($ss))->save('php://output'), $file,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
