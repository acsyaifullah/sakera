<?php

namespace App\Exports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CertificateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startYear;
    protected $endYear;
    protected $rowNumber = 0;

    public function __construct($startYear, $endYear)
    {
        $this->startYear = $startYear;
        $this->endYear   = $endYear;
    }

    public function collection()
    {
        return Document::with('user')
            ->where('title', 'Sertifikat Pelatihan / Seminar / Sosialisasi')
            ->whereBetween('period', [$this->startYear, $this->endYear])
            ->orderBy('period', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NIP',
            'NAMA KARYAWAN',
            'NAMA SERTIFIKAT',
            'TAHUN',
            'JP / JAM PELATIHAN',
            'STATUS VALIDASI'
        ];
    }

    public function map($doc): array
    {
        $this->rowNumber++;

        // Format Status berdasarkan ENUM database (pending, valid, invalid)
        switch ($doc->status) {
            case 'valid':
                $statusLabel = 'Valid';
                break;
            case 'invalid':
                $statusLabel = 'Ditolak' . ($doc->admin_note ? " ({$doc->admin_note})" : '');
                break;
            case 'pending':
            default:
                $statusLabel = 'Pending (Menunggu Validasi)';
                break;
        }

        $nipFormatted = isset($doc->user->nip) ? "'" . $doc->user->nip : '-';

        return [
            $this->rowNumber,
            $nipFormatted,
            $doc->user->name ?? '-',
            $doc->doc_title ?? '-',
            $doc->period ?? '-',
            $doc->training_hours ?? '-',
            $statusLabel
        ];
    }
}