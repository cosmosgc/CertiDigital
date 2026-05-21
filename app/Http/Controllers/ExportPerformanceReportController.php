<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Services\PerformanceReportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportPerformanceReportController extends Controller
{
    public function __construct(private readonly PerformanceReportService $reportService) {}

    public function export(CourseClass $courseClass)
    {
        $data = $this->reportService->build($courseClass);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Desempenho');

        $dates = $data['attendance_dates']->toArray();
        $students = $data['students']->toArray();
        $cls = $data['class'];

        $datesByT = $this->groupByTrimester($dates);
        $tKeys = array_keys($datesByT);
        sort($tKeys);

        $infoCols = 6;
        $attCols = count($dates);
        $gradePerT = 12;
        $totalCols = $infoCols + $attCols + 3 + count($tKeys) * $gradePerT;

        $this->setWidths($sheet, $totalCols);

        $border = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '999999']]]];
        $hFont = ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']];
        $hFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']];
        $gFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']];
        $gFont = ['bold' => true, 'size' => 10, 'color' => ['rgb' => '333333']];
        $green = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6EFCE']];
        $red   = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']];
        $altFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F8FC']];

        // === ROW 1: merge all for title ===
        $sheet->mergeCells([1, 1, $totalCols, 1]);
        $sheet->setCellValue('A1', 'TURMA: ' . $courseClass->id . ' ' . ($cls['course']['title'] ?? '') . ' (' . ($cls['name'] ?? '') . ')');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1F4E79');
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // === ROW 2: group headers ===
        $r = 2;
        $c = 1;

        // info block — merge cells A2-F2
        $sheet->mergeCells([$c, $r, $c + $infoCols - 1, $r]);
        $c += $infoCols;

        // attendance dates — merge per trimester
        foreach ($tKeys as $t) {
            $n = count($datesByT[$t]);
            $sheet->mergeCells([$c, $r, $c + $n - 1, $r]);
            $sheet->setCellValue([$c, $r], $t . ' TRIMESTRE');
            $sheet->getStyle([$c, $r, $c + $n - 1, $r])->applyFromArray(['font' => $hFont, 'fill' => $hFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
            $c += $n;
        }

        // overall frequency
        $sheet->mergeCells([$c, $r, $c + 2, $r]);
        $sheet->setCellValue([$c, $r], 'FREQUÊNCIA');
        $sheet->getStyle([$c, $r, $c + 2, $r])->applyFromArray(['font' => $hFont, 'fill' => $hFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
        $c += 3;

        // per-trimester grade blocks
        foreach ($tKeys as $t) {
            $sheet->mergeCells([$c, $r, $c + $gradePerT - 1, $r]);
            $sheet->setCellValue([$c, $r], 'DESEMPENHO ' . $t . ' TRIMESTRE');
            $sheet->getStyle([$c, $r, $c + $gradePerT - 1, $r])->applyFromArray(['font' => $gFont, 'fill' => $gFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
            $c += $gradePerT;
        }

        // === ROW 3: column headers ===
        $r = 3;
        $c = 1;

        $infoLabels = ['N', 'Aluno', 'ID', 'Idade', 'Início', 'Fim'];
        foreach ($infoLabels as $l) {
            $sheet->setCellValue([$c, $r], $l);
            $sheet->getStyle([$c, $r])->applyFromArray(['font' => $hFont, 'fill' => $hFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
            $c++;
        }

        foreach ($tKeys as $t) {
            foreach ($datesByT[$t] as $d) {
                $sheet->setCellValue([$c, $r], date_create($d['date'])->format('d/m'));
                $sheet->getStyle([$c, $r])->applyFromArray(['font' => ['size' => 8], 'fill' => $hFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
                $c++;
            }
        }

        $freqLabels = ['Aulas Dadas', 'Aulas Assistidas', 'Frequência Final'];
        foreach ($freqLabels as $l) {
            $sheet->setCellValue([$c, $r], $l);
            $sheet->getStyle([$c, $r])->applyFromArray(['font' => $hFont, 'fill' => $hFill, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
            $c++;
        }

        $tLabels = ['PRESENÇAS', 'FALTAS', 'MÉDIA', 'Ativ 1', 'Ativ 2', 'Ativ 3', 'Média Ativ', 'AU 1', 'AU 2', 'AU 3', 'Média AU', 'Nota Final'];
        foreach ($tKeys as $t) {
            foreach ($tLabels as $l) {
                $sheet->setCellValue([$c, $r], $l);
                $shade = in_array($l, ['PRESENÇAS', 'FALTAS']) ? $gFill : $hFill;
                $font = in_array($l, ['PRESENÇAS', 'FALTAS']) ? $gFont : $hFont;
                $sheet->getStyle([$c, $r])->applyFromArray(['font' => $font, 'fill' => $shade, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
                $c++;
            }
        }

        // === DATA ROWS ===
        $dr = 4;
        $attStartCol = $infoCols + 1;
        $attEndCol = $infoCols + $attCols;
        $freqColOffset = $attEndCol;

        foreach ($students as $idx => $s) {
            $c = 1;
            $row = $dr + $idx;

            $am = [];
            foreach ($s['attendances'] as $a) $am[$a['attendance_id']] = $a;

            $sheet->setCellValue([$c++, $row], $idx + 1);
            $sheet->setCellValue([$c++, $row], $s['full_name']);
            $sheet->setCellValue([$c++, $row], $s['id']);

            $age = $s['birth_date'] ? date_create($s['birth_date'])->diff(date_create('today'))->y . ' anos' : '-';
            $sheet->setCellValue([$c++, $row], $age);
            $sheet->setCellValue([$c++, $row], $this->dt($s['period_start']));
            $sheet->setCellValue([$c++, $row], $this->dt($s['period_end']));

            // attendance per date
            foreach ($dates as $d) {
                $a = $am[$d['id']] ?? null;
                $present = $a && $a['present'];
                $gv = $a && $a['grade'] !== null ? $a['grade'] : null;
                $sheet->setCellValue([$c, $row], $present ? ($gv ?? 'P') : 'F');
                $sheet->getStyle([$c, $row])->applyFromArray([
                    'fill' => $present ? $green : $red,
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => $border['borders'],
                ]);
                $c++;
            }

            // overall frequency (formulas)
            $firstAtt = $this->cl($attStartCol) . $row;
            $lastAtt  = $this->cl($attEndCol) . $row;
            $pc = $c;
            $sheet->setCellValue([$c++, $row], "=COUNTIF($firstAtt:$lastAtt,\"P\")");
            $ac = $c;
            $sheet->setCellValue([$c++, $row], "=COUNTIF($firstAtt:$lastAtt,\"F\")");
            $fc = $c;
            $sheet->setCellValue([$c++, $row], "=IF($firstAtt:$lastAtt=\"\",0,$pc/$($pc+$ac)*100)");
            // simpler formula:
            $sheet->setCellValue([$fc, $row], "=IF($pc+$ac>0,$pc/($pc+$ac)*100,0)");

            foreach ([$pc, $ac, $fc] as $x) {
                $sheet->getStyle([$x, $row])->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'borders' => $border['borders']]);
            }
            $sheet->getStyle([$fc, $row])->getNumberFormat()->setFormatCode('0.00');

            // per-trimester grades
            $tgT = [];
            foreach ($s['trimester_grades'] as $tg) $tgT[$tg['trimester']] = $tg;
            $tsT = [];
            foreach ($s['trimester_summaries'] as $ts) $tsT[$ts['trimester']] = $ts;

            foreach ($tKeys as $t) {
                $tg = $tgT[$t] ?? [];
                $ts = $tsT[$t] ?? [];

                $sheet->setCellValue([$c++, $row], $ts['present_count'] ?? '');
                $sheet->setCellValue([$c++, $row], $ts['absent_count'] ?? '');
                $attGradesT = array_values(array_filter($s['attendances'], fn($a) => $a['trimester'] === $t && $a['grade'] !== null));
                $avgAttGrade = count($attGradesT) > 0 ? round(array_sum(array_column($attGradesT, 'grade')) / count($attGradesT), 2) : '';
                $sheet->setCellValue([$c++, $row], $avgAttGrade);
                $sheet->setCellValue([$c++, $row], $tg['activity_grade_1'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['activity_grade_2'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['activity_grade_3'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['activities_average'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['au_grade_1'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['au_grade_2'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['au_grade_3'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['au_average'] ?? '');
                $sheet->setCellValue([$c++, $row], $tg['final_grade'] ?? '');
            }
        }

        // === AVERAGE ROW ===
        $ar = $dr + count($students);
        $sheet->setCellValue([1, $ar], 'Média da Turma');
        $sheet->mergeCells([1, $ar, $infoCols, $ar]);
        $sheet->getStyle([1, $ar, $infoCols, $ar])->applyFromArray(['font' => ['bold' => true, 'size' => 10], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']], 'borders' => $border['borders']]);

        // Average per attendance date (% present)
        for ($c = $attStartCol; $c <= $attEndCol; $c++) {
            $colL = $this->cl($c);
            $sheet->setCellValue([$c, $ar], "=IF(COUNTA($colL$dr:$colL" . ($ar-1) . ")>0,(COUNTA($colL$dr:$colL" . ($ar-1) . ")-COUNTIF($colL$dr:$colL" . ($ar-1) . ',"F"))/COUNTA(' . "$colL$dr:$colL" . ($ar-1) . ")*100,0)");
            $sheet->getStyle([$c, $ar])->getNumberFormat()->setFormatCode('0.0');
        }

        // Average frequency columns
        $pc = $attEndCol + 1;
        $ac = $attEndCol + 2;
        $fcp = $attEndCol + 3;
        foreach ([$pc, $ac, $fcp] as $fc) {
            $colL = $this->cl($fc);
            $sheet->setCellValue([$fc, $ar], "=AVERAGE($colL$dr:$colL" . ($ar-1) . ")");
        }
        $sheet->getStyle([$fcp, $ar])->getNumberFormat()->setFormatCode('0.00');

        // Average per-trimester grade columns
        $c = $attEndCol + 4;
        foreach ($tKeys as $t) {
            for ($g = 0; $g < $gradePerT; $g++) {
                $colL = $this->cl($c);
                $sheet->setCellValue([$c, $ar], "=IF(COUNTA($colL$dr:$colL" . ($ar-1) . ")>0,AVERAGE($colL$dr:$colL" . ($ar-1) . '),"")');
                $sheet->getStyle([$c, $ar])->getNumberFormat()->setFormatCode('0.00');
                $c++;
            }
        }

        // Apply borders, alignment, and alternating row fills
        $gCols = []; // per-trimester MÉDIA and Nota Final column indices
        $c = $infoCols + $attCols + 3 + 1; // skip info + att + freq, start of grade blocks
        foreach ($tKeys as $t) {
            $gCols[] = $c + 2; // MÉDIA (3rd col of block, 0-indexed: 0=Pres,1=Falta,2=Média)
            $gCols[] = $c + 11; // Nota Final (12th col of block)
            $c += $gradePerT;
        }

        for ($r = $dr; $r <= $ar; $r++) {
            $isEven = ($r - $dr) % 2 === 0 && $r < $ar;
            for ($c = 1; $c <= $totalCols; $c++) {
                $style = [
                    'borders' => $border['borders'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                // Only apply alternating fill outside attendance columns (preserve green/red P/F)
                if ($isEven && ($c < $attStartCol || $c > $attEndCol)) {
                    $style['fill'] = $altFill;
                }
                $sheet->getStyle([$c, $r])->applyFromArray($style);

                // Bold MÉDIA and Nota Final columns
                if (in_array($c, $gCols)) {
                    $sheet->getStyle([$c, $r])->getFont()->setBold(true);
                }
            }
            $sheet->getStyle([2, $r])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = ($cls['course']['title'] ?? 'relatorio') . ' - ' . ($cls['name'] ?? 'turma') . '.xlsx';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\. áàâãéêíóôõúçÁÀÂÃÉÊÍÓÔÕÚÇ]/u', '_', $filename);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function groupByTrimester(array $dates): array
    {
        $g = [];
        foreach ($dates as $d) {
            $t = $d['trimester'];
            if (!isset($g[$t])) $g[$t] = [];
            $g[$t][] = $d;
        }
        return $g;
    }

    private function setWidths(Worksheet $s, int $t): void
    {
        $s->getColumnDimension('A')->setWidth(4);
        $s->getColumnDimension('B')->setWidth(35);
        $s->getColumnDimension('C')->setWidth(6);
        $s->getColumnDimension('D')->setWidth(10);
        $s->getColumnDimension('E')->setWidth(12);
        $s->getColumnDimension('F')->setWidth(12);
        for ($i = 7; $i <= $t; $i++) {
            $s->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(10);
        }
    }

    private function cl(int $c): string { return Coordinate::stringFromColumnIndex($c); }

    private function dt(?string $v): string
    {
        if (!$v || $v === '-') return '-';
        $d = date_create($v);
        return $d ? $d->format('d/m/Y') : '-';
    }
}
