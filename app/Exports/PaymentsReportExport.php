<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsReportExport implements FromCollection, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $reportData;

    protected $dateRange;

    public function __construct($reportData, $dateRange)
    {
        $this->reportData = $reportData;
        $this->dateRange = $dateRange;
    }

    public function collection()
    {
        return collect($this->reportData['payments']);
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal & Waktu',
            'Tanggal Pemeriksaan',
            'Nama Pasien',
            'Jenis Layanan',
            'Metode Pembayaran',
            'Bukti Pembayaran',
            'Total Bayar (IDR)',
            'Status',
        ];
    }

    public function map($payment): array
    {
        static $no = 1;

        $proofPath = $payment['payment_proof_url'] ?? null;

        return [
            $no++,
            $payment['no_transaction'] ?? 'TXN-'.str_pad($no - 1, 6, '0', STR_PAD_LEFT),
            $payment['formatted_date'] ?? '-',
            $payment['formatted_exam_date'] ?? '-',
            $payment['patient_name'] ?? '-',
            $payment['service_type'] ?? 'Screening/Obat',
            $payment['payment_method'] ?? 'Transfer/Online',
            $proofPath ?? 'Tidak Ada',
            isset($payment['amount_paid']) ? 'Rp '.number_format($payment['amount_paid'], 0, ',', '.') : '-',
            'Berhasil',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $dataRowCount = $this->collection()->count();
        $lastRow = 7 + $dataRowCount;

        return [
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ],
            "A8:I{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            "I8:I{$lastRow}" => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'font' => ['bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI KLINIK GUNUNG');
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->setCellValue('A2', 'Periode: '.$this->dateRange['label']);
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '4B5563']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A4', 'RINGKASAN LAPORAN');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1F2937']],
                ]);

                $sheet->setCellValue('A5', 'Total Transaksi: '.$this->reportData['totalTransactions']);
                $sheet->setCellValue('F5', 'Total Pemasukan: Rp '.number_format($this->reportData['totalOverallIncome'], 0, ',', '.'));
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
                ]);

                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(7)->setRowHeight(20);

                $lastRow = 7 + $this->collection()->count();
                $footerRow = $lastRow + 2;
                $sheet->setCellValue("A{$footerRow}", 'Laporan dibuat pada: '.now()->format('d/m/Y H:i:s').' WIB');
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
                ]);

                // Hyperlink bukti pembayaran
                $currentRow = 8;
                foreach ($this->collection() as $payment) {
                    if (! empty($payment['payment_proof_url'])) {
                        $link = $payment['payment_proof_url'];
                        $sheet->getCell("H{$currentRow}")->getHyperlink()->setUrl($link);
                        $sheet->getStyle("H{$currentRow}")->applyFromArray([
                            'font' => ['color' => ['rgb' => '0000FF'], 'underline' => true],
                        ]);
                    }
                    $currentRow++;
                }
            },
        ];
    }
}
