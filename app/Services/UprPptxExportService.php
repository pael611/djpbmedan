<?php

namespace App\Services;

use App\Models\Bidang;
use App\Models\NilaiAwalRisiko;
use App\Models\ProgresMitigasi;
use App\Models\RencanaMitigasi;
use App\Models\Risiko;
use DOMDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\Table\Cell;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;

class UprPptxExportService
{
    private const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private const ROMAWI = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
    ];

    private const STATUS_VERIFIKASI = [
        'pending' => 'Menunggu validasi',
        'approved' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];

    private const WARNA_MATRIKS = [
        'biru' => 'FF5CC8FF',
        'hijau' => 'FF1EEB77',
        'kuning' => 'FFFFFF00',
        'oranye' => 'FFF79646',
        'merah' => 'FFF24C4C',
    ];

    private const WARNA_DAFTAR = [
        'merah' => 'FFFF0000',
        'oranye' => 'FFFFC000',
        'kuning' => 'FFFFFF00',
        'hijau' => 'FF92D050',
        'biru' => 'FF5B9BD5',
    ];

    private int $logoCounter = 0;

    /**
     * Menghasilkan file .pptx Rapat UPR (Cover + Peta Risiko 5x5 + Divider & Detail per Bidang)
     * dan mengembalikan path file sementara.
     */
    public function generate(
        int $tahun,
        ?int $bulan = null,
        ?int $triwulan = null,
        ?int $bidangId = null
    ): string {
        $this->logoCounter = 0;

        $bulanRapat = $bulan ?? now()->month;
        $bulanAktual = $bulan ?? ($triwulan ? $triwulan * 3 : 12);
        $tahunAktual = $tahun;
        $triwulanEfektif = $triwulan ?? ($bulan ? (int) ceil($bulan / 3) : null);
        $kuartalAktif = $triwulanEfektif ?? now()->quarter;
        $triwulanRekomendasi = min(4, $kuartalAktif + 1);

        $namaBulanRapat = self::BULAN[$bulanRapat] ?? 'Oktober';
        $namaBulanAktual = self::BULAN[$bulanAktual] ?? 'September';
        $labelAwalTahun = 'P'.substr((string) $tahun, -2);
        $labelKuartal = 'Q'.$kuartalAktif;

        $presentation = new PhpPresentation();
        $presentation->getLayout()->setDocumentLayout(DocumentLayout::LAYOUT_SCREEN_16X9, true);
        $presentation->getDocumentProperties()
            ->setCreator('PARIBAN — Kanwil DJPb Provinsi Sumatera Utara')
            ->setTitle("Rapat UPR Kanwil {$namaBulanRapat} {$tahun}")
            ->setSubject('Progress Mitigasi Risiko');

        // Slide 1: Cover
        $this->buildCoverSlide(
            $presentation->getActiveSlide(),
            $namaBulanRapat,
            $namaBulanAktual,
            $tahun,
            $tahunAktual
        );

        // Ambil data Dashboard (Matriks 5x5 + 10 Daftar Kejadian Risiko)
        [$matriksSaatIni, $daftarKejadian] = $this->loadDashboardData(
            $tahun,
            $triwulanEfektif,
            $kuartalAktif,
            $bidangId
        );

        // Slide 2: Peta Risiko Kanwil (Matriks 5x5 + Legenda + Tabel 10 Kejadian Risiko)
        $this->buildPetaRisikoSlide(
            $presentation->createSlide(),
            $tahun,
            $labelAwalTahun,
            $labelKuartal,
            $matriksSaatIni,
            $daftarKejadian
        );

        // Slide 3+: Divider Bidang + Detail Risiko per Bidang
        $bidangList = Bidang::query()
            ->when($bidangId, fn (Builder $q) => $q->where('id', $bidangId))
            ->orderBy('id')
            ->get();

        foreach ($bidangList as $bidang) {
            $risikoBidang = Risiko::query()
                ->where('bidang_id', $bidang->id)
                ->where('tahun', $tahun)
                ->with([
                    'bidang:id,kode,nama',
                    'rencana' => fn (HasMany $q) => $q
                        ->select(['id', 'risiko_id', 'deskripsi_rencana', 'status_verifikasi'])
                        ->orderBy('id'),
                    'rencana.progres' => function (HasMany $q) use ($bulanAktual, $tahunAktual): void {
                        $q->select(['id', 'rencana_id', 'deskripsi_progres', 'bulan', 'tahun', 'status_verifikasi'])
                            ->getQuery()
                            ->where(function (Builder $sub) use ($bulanAktual, $tahunAktual): void {
                                $sub->where('tahun', '<', $tahunAktual)
                                    ->orWhere(function (Builder $sameYear) use ($bulanAktual, $tahunAktual): void {
                                        $sameYear->where('tahun', $tahunAktual)
                                            ->where('bulan', '<=', $bulanAktual);
                                    });
                            })
                            ->orderBy('tahun')
                            ->orderBy('bulan')
                            ->orderBy('id');
                    },
                    'rekomendasiRisiko' => fn (HasMany $q) => $q
                        ->where('tahun', $tahun)
                        ->orderByDesc('triwulan'),
                ])
                ->orderByPriority()
                ->get();

            if ($risikoBidang->isEmpty()) {
                continue;
            }

            $this->buildBidangDividerSlide($presentation->createSlide(), $bidang->nama, $tahun);

            foreach ($risikoBidang as $risiko) {
                $this->buildRisikoSlides(
                    $presentation,
                    $risiko,
                    $namaBulanAktual,
                    $tahunAktual,
                    self::ROMAWI[$triwulanRekomendasi] ?? 'IV',
                    $tahun
                );
            }
        }

        $baseTemp = tempnam(sys_get_temp_dir(), 'pariban_pptx_');
        if ($baseTemp === false) {
            throw new \RuntimeException('Gagal membuat file sementara untuk export PPTX.');
        }
        @unlink($baseTemp);
        $tempPath = $baseTemp.'.pptx';

        $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * @return array{0: array<int, array<int, Collection<int, Risiko>>>, 1: array<int, array<string, mixed>>}
     */
    private function loadDashboardData(
        int $tahun,
        ?int $triwulanEfektif,
        int $kuartalAktif,
        ?int $bidangId
    ): array {
        $risiko = Risiko::with([
            'bidang',
            'rekomendasiRisiko' => fn ($query) => $query
                ->where('tahun', $tahun)
                ->when($triwulanEfektif, fn ($q) => $q->where('triwulan', $triwulanEfektif)),
        ])
            ->where('tahun', $tahun)
            ->when($triwulanEfektif, fn ($q) => $q->where('triwulan', $triwulanEfektif))
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
            ->orderByPriority()
            ->get();

        $matriks = [];
        for ($k = 1; $k <= 5; $k++) {
            for ($d = 1; $d <= 5; $d++) {
                $matriks[$k][$d] = collect();
            }
        }
        foreach ($risiko as $r) {
            $matriks[$r->level_kemungkinan][$r->level_dampak]->push($r);
        }

        $risikoTahunIni = Risiko::with([
            'rekomendasiRisiko' => fn ($query) => $query
                ->where('tahun', $tahun)
                ->orderByDesc('triwulan'),
        ])
            ->where('tahun', $tahun)
            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
            ->orderByDesc('triwulan')
            ->orderByPriority()
            ->get()
            ->groupBy('kejadian_risiko');

        $nilaiAwalMap = NilaiAwalRisiko::query()
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('kejadian_risiko');

        $daftarKejadian = [];
        foreach (Risiko::DAFTAR_KEJADIAN_RISIKO as $nomor => $namaKejadian) {
            $nilaiAwal = $nilaiAwalMap->get($namaKejadian)?->nilai_awal;
            $kandidat = $risikoTahunIni->get($namaKejadian, collect());
            $risikoKuartal = $kandidat->firstWhere('triwulan', $kuartalAktif)
                ?? $kandidat->first(fn (Risiko $item): bool => (int) $item->triwulan <= $kuartalAktif)
                ?? $kandidat->first();

            $nilaiKuartal = null;
            if ($risikoKuartal) {
                $rekomendasiKuartal = $risikoKuartal->rekomendasiRisiko->firstWhere('triwulan', $kuartalAktif);
                $nilaiKuartal = $rekomendasiKuartal?->rekomendasi_besaran ?? (int) $risikoKuartal->besaran_risiko;
            }

            $daftarKejadian[] = [
                'no' => $nomor,
                'kejadian_risiko' => $namaKejadian,
                'nilai_awal' => $nilaiAwal,
                'kode_awal' => $nilaiAwal ? Risiko::klasifikasi($nilaiAwal)['kode'] : null,
                'nilai_kuartal' => $nilaiKuartal,
                'kode_kuartal' => $nilaiKuartal ? Risiko::klasifikasi($nilaiKuartal)['kode'] : null,
            ];
        }

        return [$matriks, $daftarKejadian];
    }

    private function buildCoverSlide(
        Slide $slide,
        string $namaBulanRapat,
        string $namaBulanAktual,
        int $tahun,
        int $tahunAktual
    ): void {
        $this->addLogo($slide, 28, 20, 52);

        $this->createFilledBox($slide, 430, 26, 510, 464, 'FFC4C400');

        $badge = $this->createFilledBox($slide, 470, 56, 430, 48, 'FFDCE6D5', 'FF005FAC');
        $badge->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $badge->createTextRun('PROGRESS MITIGASI RISIKO')->getFont()
            ->setName('Calibri')->setBold(true)->setSize(22)->setColor(new Color('FF1F2937'));

        $title = $slide->createRichTextShape()->setOffsetX(28)->setOffsetY(132)->setWidth(385)->setHeight(110);
        $this->writeLines($title, [
            'RAPAT UPR BULAN',
            strtoupper($namaBulanRapat).' '.$tahun,
        ], 28, true, 'FF005FAC');

        $sub = $slide->createRichTextShape()->setOffsetX(28)->setOffsetY(258)->setWidth(385)->setHeight(135);
        $this->writeLines($sub, [
            'PROGRESS MITIGASI RISIKO',
            'S.D. BULAN '.strtoupper($namaBulanAktual).' '.$tahunAktual,
            'KANWIL DJPb PROVINSI',
            'SUMATERA UTARA TAHUN '.$tahun,
        ], 15, true, 'FF111827');

        $this->createFilledBox($slide, 28, 405, 360, 2, 'FF005FAC');
        $footer = $slide->createRichTextShape()->setOffsetX(28)->setOffsetY(416)->setWidth(370)->setHeight(55);
        $this->writeLines($footer, [
            'Kanwil DJPb Provinsi Sumatera Utara',
            (string) $tahun,
        ], 13, true, 'FF334155');

        $this->createFilledBox($slide, 0, 490, 420, 18, 'FFFAB715');
        $this->createFilledBox($slide, 0, 508, 960, 32, 'FF0066B3');
    }

    /**
     * @param  array<int, array<int, Collection<int, Risiko>>>  $matriksSaatIni
     * @param  array<int, array<string, mixed>>  $daftarKejadian
     */
    private function buildPetaRisikoSlide(
        Slide $slide,
        int $tahun,
        string $labelAwalTahun,
        string $labelKuartal,
        array $matriksSaatIni,
        array $daftarKejadian
    ): void {
        $heading = $slide->createRichTextShape()->setOffsetX(24)->setOffsetY(12)->setWidth(430)->setHeight(32);
        $heading->createTextRun("Peta Risiko Kanwil DJPb Sumut {$tahun}")->getFont()
            ->setName('Calibri')->setBold(true)->setSize(18)->setColor(new Color('FF111827'));

        // Header atas Matriks (tanpa colSpan agar 100% valid di Microsoft PowerPoint)
        $hdrLeft = $this->createFilledBox($slide, 24, 48, 90, 22, 'FFF8FAFC', 'FF374151');
        $hdrLeft->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $hdrLeft->createTextRun('Matriks 5x5')->getFont()
            ->setName('Calibri')->setBold(true)->setSize(8)->setColor(new Color('FF111827'));

        $hdrRight = $this->createFilledBox($slide, 114, 48, 325, 22, 'FFF8FAFC', 'FF374151');
        $hdrRight->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $hdrRight->createTextRun('Level Dampak')->getFont()
            ->setName('Calibri')->setBold(true)->setSize(9)->setColor(new Color('FF111827'));

        // Tabel Matriks 5x5 (7 kolom murni tanpa colSpan/rowSpan)
        $matriksTable = $slide->createTableShape(7);
        $matriksTable->setOffsetX(24)->setOffsetY(70)->setWidth(415);

        $colWidths = [22, 68, 65, 65, 65, 65, 65];
        $labelD = [1 => '1-Tdk Sig.', 2 => '2-Minor', 3 => '3-Moderat', 4 => '4-Sig.', 5 => '5-Sgt Sig.'];
        $labelK = [5 => 'Hampir Pasti', 4 => 'Sering', 3 => 'Kadang', 2 => 'Jarang', 1 => 'Sgt Jarang'];

        $rHeader = $matriksTable->createRow()->setHeight(20);
        $this->styleCell($rHeader->nextCell()->setWidth($colWidths[0]), 'K', 'FFFFFFFF', 'FF111827', 7, true, Alignment::HORIZONTAL_CENTER, 'FF374151');
        $this->styleCell($rHeader->nextCell()->setWidth($colWidths[1]), 'Kemungkinan', 'FFFFFFFF', 'FF111827', 7, true, Alignment::HORIZONTAL_LEFT, 'FF374151');
        for ($d = 1; $d <= 5; $d++) {
            $this->styleCell($rHeader->nextCell()->setWidth($colWidths[$d + 1]), $labelD[$d], 'FFFFFFFF', 'FF111827', 7, false, Alignment::HORIZONTAL_CENTER, 'FF374151');
        }

        for ($k = 5; $k >= 1; $k--) {
            $row = $matriksTable->createRow()->setHeight(52);
            $this->styleCell($row->nextCell()->setWidth($colWidths[0]), (string) $k, 'FFFFFFFF', 'FF111827', 8, true, Alignment::HORIZONTAL_CENTER, 'FF374151');
            $this->styleCell($row->nextCell()->setWidth($colWidths[1]), $labelK[$k], 'FFFFFFFF', 'FF111827', 7, false, Alignment::HORIZONTAL_LEFT, 'FF374151');

            for ($d = 1; $d <= 5; $d++) {
                $besaran = Risiko::hitungBesaran($k, $d);
                $kode = Risiko::klasifikasi($besaran)['kode'];
                $bg = self::WARNA_MATRIKS[$kode] ?? 'FFFFFFFF';

                $ids = $matriksSaatIni[$k][$d]->pluck('id')->map(fn ($id) => '#'.$id)->implode(' ');
                $cell = $row->nextCell()->setWidth($colWidths[$d + 1]);
                $this->styleCell($cell, (string) $besaran, $bg, 'FF111827', 14, true, Alignment::HORIZONTAL_LEFT, 'FF374151', Alignment::VERTICAL_TOP);
                if ($ids !== '') {
                    $p = $cell->createParagraph();
                    $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $p->createTextRun($ids)->getFont()->setName('Calibri')->setBold(true)->setSize(7)->setColor(new Color('FF111827'));
                }
            }
        }

        // Tabel Legenda (kiri bawah)
        $legenda = $slide->createTableShape(3);
        $legenda->setOffsetX(24)->setOffsetY(368)->setWidth(315);
        $legWidths = [130, 100, 85];

        $lh = $legenda->createRow()->setHeight(22);
        $this->styleCell($lh->nextCell()->setWidth($legWidths[0]), 'Level Risiko', 'FFFFFFFF', 'FF111827', 9, true, Alignment::HORIZONTAL_CENTER, 'FF374151');
        $this->styleCell($lh->nextCell()->setWidth($legWidths[1]), 'Besaran Risiko', 'FFFFFFFF', 'FF111827', 9, true, Alignment::HORIZONTAL_CENTER, 'FF374151');
        $this->styleCell($lh->nextCell()->setWidth($legWidths[2]), 'Warna', 'FFFFFFFF', 'FF111827', 9, true, Alignment::HORIZONTAL_CENTER, 'FF374151');

        $legRows = [
            ['Sangat Tinggi (5)', '20 - 25', 'Merah', 'FFFF0000'],
            ['Tinggi (4)', '16 - 19', 'Oranye', 'FFFFC000'],
            ['Sedang (3)', '12 - 15', 'Kuning', 'FFFFFF00'],
            ['Rendah (2)', '6 - 11', 'Hijau', 'FF00B050'],
            ['Sangat Rendah (1)', '1 - 5', 'Biru', 'FF5B9BD5'],
        ];
        foreach ($legRows as [$lvl, $range, $warna, $hex]) {
            $lr = $legenda->createRow()->setHeight(22);
            $this->styleCell($lr->nextCell()->setWidth($legWidths[0]), $lvl, 'FFFCECE2', 'FF111827', 8, false, Alignment::HORIZONTAL_CENTER, 'FF374151');
            $this->styleCell($lr->nextCell()->setWidth($legWidths[1]), $range, 'FFFCECE2', 'FF111827', 8, false, Alignment::HORIZONTAL_CENTER, 'FF374151');
            $this->styleCell($lr->nextCell()->setWidth($legWidths[2]), $warna, $hex, 'FF111827', 8, true, Alignment::HORIZONTAL_CENTER, 'FF374151');
        }

        // Tabel Daftar Kejadian Risiko (kanan penuh)
        $daftar = $slide->createTableShape(4);
        $daftar->setOffsetX(460)->setOffsetY(12)->setWidth(486);
        $dWidths = [34, 356, 48, 48];

        $dh = $daftar->createRow()->setHeight(30);
        $this->styleCell($dh->nextCell()->setWidth($dWidths[0]), 'No', 'FF4BACC6', 'FFFFFFFF', 10, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
        $this->styleCell($dh->nextCell()->setWidth($dWidths[1]), 'Kejadian Risiko', 'FF4BACC6', 'FFFFFFFF', 10, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
        $this->styleCell($dh->nextCell()->setWidth($dWidths[2]), $labelAwalTahun, 'FF4BACC6', 'FFFFFFFF', 10, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
        $this->styleCell($dh->nextCell()->setWidth($dWidths[3]), $labelKuartal, 'FF4BACC6', 'FFFFFFFF', 10, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');

        foreach ($daftarKejadian as $item) {
            $bgBaris = $item['kode_awal']
                ? (self::WARNA_DAFTAR[$item['kode_awal']] ?? 'FFF8FAFC')
                : ($item['kode_kuartal'] ? (self::WARNA_DAFTAR[$item['kode_kuartal']] ?? 'FFF8FAFC') : 'FFF8FAFC');
            $bgAwal = $item['kode_awal'] ? (self::WARNA_DAFTAR[$item['kode_awal']] ?? 'FFF8FAFC') : 'FFF8FAFC';
            $bgKuartal = $item['kode_kuartal'] ? (self::WARNA_DAFTAR[$item['kode_kuartal']] ?? 'FFF8FAFC') : 'FFF8FAFC';

            $dr = $daftar->createRow()->setHeight(48);
            $this->styleCell($dr->nextCell()->setWidth($dWidths[0]), (string) $item['no'], $bgBaris, 'FF111827', 10, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
            $this->styleCell($dr->nextCell()->setWidth($dWidths[1]), (string) $item['kejadian_risiko'], $bgBaris, 'FF111827', 10, false, Alignment::HORIZONTAL_LEFT, 'FFFFFFFF');
            $this->styleCell($dr->nextCell()->setWidth($dWidths[2]), $item['nilai_awal'] !== null ? (string) $item['nilai_awal'] : '-', $bgAwal, 'FF111827', 11, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
            $this->styleCell($dr->nextCell()->setWidth($dWidths[3]), $item['nilai_kuartal'] !== null ? (string) $item['nilai_kuartal'] : '-', $bgKuartal, 'FF111827', 11, true, Alignment::HORIZONTAL_CENTER, 'FFFFFFFF');
        }
    }

    private function buildBidangDividerSlide(Slide $slide, string $namaBidang, int $tahun): void
    {
        $this->createFilledBox($slide, 0, 0, 340, 100, 'FF0066B3');
        $this->addLogo($slide, 26, 22, 52);

        $center = $slide->createRichTextShape()->setOffsetX(80)->setOffsetY(215)->setWidth(800)->setHeight(90);
        $center->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $center->createTextRun(strtoupper($namaBidang))->getFont()
            ->setName('Calibri')->setBold(true)->setSize(44)->setColor(new Color('FF111827'));

        $footerBar = $this->createFilledBox($slide, 0, 506, 960, 34, 'FF0066B3');
        $footerBar->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $footerBar->createTextRun("© {$tahun} Direktorat Jenderal Perbendaharaan — Kanwil DJPb Provinsi Sumatera Utara")
            ->getFont()->setName('Calibri')->setSize(11)->setColor(new Color('FFFFFFFF'));
    }

    private function buildRisikoSlides(
        PhpPresentation $presentation,
        Risiko $risiko,
        string $namaBulanAktual,
        int $tahunAktual,
        string $romawiRekomendasi,
        int $tahunRekomendasi
    ): void {
        // Susun daftar Rencana Mitigasi beserta status validasi bukti
        $rencanaEntries = [];
        foreach ($risiko->rencana as $rencana) {
            /** @var RencanaMitigasi $rencana */
            $status = $this->formatStatusVerifikasi($rencana->status_verifikasi);
            $lines = $this->extractRichTextLines($rencana->deskripsi_rencana);
            $lastIndex = count($lines) - 1;
            foreach ($lines as $idx => $line) {
                $rencanaEntries[] = [
                    'text' => $line,
                    'status' => $idx === $lastIndex ? $status : null,
                ];
            }
        }

        // Susun daftar Progress Mitigasi dari model ProgresMitigasi (urut kronologis + status_verifikasi)
        $progresEntries = [];
        $semuaProgres = $risiko->rencana
            ->flatMap(fn (RencanaMitigasi $rencana) => $rencana->progres)
            ->sortBy([
                ['tahun', 'asc'],
                ['bulan', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        foreach ($semuaProgres as $progres) {
            /** @var ProgresMitigasi $progres */
            $status = $this->formatStatusVerifikasi($progres->status_verifikasi);
            $periodePrefix = "{$progres->bulan}/{$progres->tahun}: ";
            $lines = $this->extractRichTextLines($progres->deskripsi_progres);
            foreach ($lines as $idx => $line) {
                $progresEntries[] = [
                    'text' => ($idx === 0 ? $periodePrefix : '').$line,
                    'status' => $status,
                ];
            }
        }

        $splitProgress = count($progresEntries) > 5;
        $slide1 = $presentation->createSlide();
        $this->addRisikoHeader($slide1, $risiko);

        // Kotak kiri: Besaran/Level Risiko Aktual
        $this->addOrangeTab($slide1, 92, 92, 330, "Besaran/Level Risiko Aktual s.d. {$namaBulanAktual} {$tahunAktual}");
        $boxAktual = $this->createFilledBox($slide1, 92, 118, 360, 88, 'FFFFFFFF', 'FF111827');
        $boxAktual->createTextRun(
            "Besaran/Level risiko aktual berada pada level {$risiko->besaran_risiko} ({$risiko->klasifikasi['label']}), ".
            "dengan Level Kemungkinan {$risiko->level_kemungkinan} dan Level Dampak {$risiko->level_dampak} ".
            "(TW {$risiko->triwulan}/{$risiko->tahun})."
        )->getFont()->setName('Calibri')->setBold(true)->setSize(11)->setColor(new Color('FF111827'));

        // Kotak kanan: Rekomendasi Besaran Risiko
        $this->addOrangeTab($slide1, 515, 92, 355, "Rekomendasi Besaran Risiko Triwulan {$romawiRekomendasi} {$tahunRekomendasi}");
        $boxRek = $this->createFilledBox($slide1, 515, 118, 375, 88, 'FFFFFFFF', 'FF111827');
        $rek = $risiko->rekomendasiRisiko->first();
        if ($rek) {
            $rekLines = [trim((string) $rek->deskripsi_rekomendasi)];
            if ($rek->rekomendasi_besaran) {
                $rekLines[] = "Proyeksi: {$rek->rekomendasi_level_kemungkinan} x {$rek->rekomendasi_level_dampak} = {$rek->rekomendasi_besaran} ({$rek->klasifikasiRekomendasi['label']}).";
            }
            $this->writeLines($boxRek, $rekLines, 11, true, 'FF111827');
        } else {
            $boxRek->createTextRun('Belum ada rekomendasi manual untuk periode yang dipilih.')->getFont()
                ->setName('Calibri')->setItalic(true)->setSize(11)->setColor(new Color('FF6B7280'));
        }

        // Kotak Rencana Mitigasi (disertai status validasi bukti)
        $rencanaHeight = $splitProgress ? 245 : 130;
        $this->addOrangeTab($slide1, 92, 218, 280, 'Rencana Mitigasi');
        $boxRencana = $this->createFilledBox($slide1, 92, 244, 800, $rencanaHeight, 'FFFFFFFF', 'FF111827');
        $this->writeNumberedEntries($boxRencana, $rencanaEntries, 'Belum ada rencana mitigasi.', 11);

        if (! $splitProgress) {
            $this->addOrangeTab($slide1, 92, 386, 280, 'Progress Mitigasi Risiko');
            $boxProgres = $this->createFilledBox($slide1, 92, 412, 800, 112, 'FFFFFFFF', 'FF111827');
            $this->writeNumberedEntries($boxProgres, $progresEntries, 'Belum ada progres mitigasi sampai periode ini.', 10);

            return;
        }

        // Progres panjang -> pecah ke slide lanjutan (maks 11 baris per slide)
        $chunks = array_chunk($progresEntries, 11);
        $offsetNumber = 1;
        foreach ($chunks as $chunk) {
            $contSlide = $presentation->createSlide();
            $this->addRisikoHeader($contSlide, $risiko);
            $this->addOrangeTab($contSlide, 92, 92, 280, 'Progress Mitigasi Risiko');
            $boxProgres = $this->createFilledBox($contSlide, 92, 118, 800, 398, 'FFFFFFFF', 'FF111827');
            $this->writeNumberedEntries($boxProgres, $chunk, 'Belum ada progres mitigasi.', 10, $offsetNumber);
            $offsetNumber += count($chunk);
        }
    }

    private function formatStatusVerifikasi(?string $status): string
    {
        return self::STATUS_VERIFIKASI[$status ?? ''] ?? 'Belum divalidasi';
    }

    private function addRisikoHeader(Slide $slide, Risiko $risiko): void
    {
        $header = $this->createFilledBox($slide, 92, 14, 800, 68, 'FF5B9BD5', 'FF41719C');
        $this->writeLines($header, [
            'Sasaran  : '.trim((string) $risiko->sasaran),
            'Risiko     : '.trim((string) $risiko->kejadian_risiko),
        ], 12, true, 'FFFFFFFF');

        $badge = $this->createFilledBox($slide, 780, 28, 96, 36, 'FF7E9046', 'FFFFFFFF');
        $badge->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $badge->createTextRun($risiko->bidang->nama ?? $risiko->bidang->kode ?? 'UPR')
            ->getFont()->setName('Calibri')->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
    }

    private function addOrangeTab(Slide $slide, int $x, int $y, int $w, string $title): void
    {
        $tab = $this->createFilledBox($slide, $x, $y, $w, 26, 'FFFF9900');
        $tab->getActiveParagraph()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $tab->createTextRun($title)->getFont()
            ->setName('Calibri')->setBold(true)->setSize(11)->setColor(new Color('FF111827'));
    }

    private function addLogo(Slide $slide, int $x, int $y, int $height): void
    {
        $logoPath = public_path('image/Logo.png');
        if (! is_file($logoPath) || ! is_readable($logoPath)) {
            return;
        }

        $this->logoCounter++;
        $logo = $slide->createDrawingShape();
        $logo->setName('Logo_'.$this->logoCounter)
            ->setDescription('Logo DJPb')
            ->setPath($logoPath)
            ->setHeight($height)
            ->setOffsetX($x)
            ->setOffsetY($y);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function writeLines(
        RichText $box,
        array $lines,
        int $fontSize,
        bool $bold,
        string $colorHex
    ): void {
        foreach ($lines as $idx => $line) {
            $p = $idx === 0 ? $box->getActiveParagraph() : $box->createParagraph();
            $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $p->createTextRun($line)->getFont()
                ->setName('Calibri')->setBold($bold)->setSize($fontSize)->setColor(new Color($colorHex));
        }
    }

    /**
     * Menulis daftar bernomor beserta status validasi bukti dalam warna muted.
     *
     * @param  array<int, array{text: string, status: ?string}>  $entries
     */
    private function writeNumberedEntries(
        RichText $box,
        array $entries,
        string $emptyText,
        int $fontSize,
    ): void {
        if ($entries === []) {
            $box->createTextRun($emptyText)->getFont()
                ->setName('Calibri')->setItalic(true)->setSize($fontSize)->setColor(new Color('FF6B7280'));

            return;
        }

        foreach ($entries as $idx => $entry) {
            $p = $idx === 0 ? $box->getActiveParagraph() : $box->createParagraph();
            $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $p->createTextRun("{$entry['text']}")->getFont()
                ->setName('Calibri')->setBold(true)->setSize($fontSize)->setColor(new Color('FF111827'));

            if (! empty($entry['status'])) {
                $p->createTextRun(" ({$entry['status']})")->getFont()
                    ->setName('Calibri')->setBold(false)->setItalic(true)->setSize(max(9, $fontSize - 1))->setColor(new Color('FF6B7280'));
            }
        }
    }

    /**
     * Mengonversi HTML hasil Quill (ol/ul/li/p) menjadi baris teks bersih untuk slide PowerPoint.
     *
     * @return array<int, string>
     */
    private function extractRichTextLines(string $html): array
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $lines = [];
        $listItems = $dom->getElementsByTagName('li');
        if ($listItems->length > 0) {
            foreach ($listItems as $li) {
                $text = trim(preg_replace('/\s+/', ' ', $li->textContent) ?? '');
                if ($text !== '') {
                    $lines[] = $text;
                }
            }

            return $lines;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');

        return $plain !== '' ? [$plain] : [];
    }

    private function createFilledBox(
        Slide $slide,
        int $x,
        int $y,
        int $w,
        int $h,
        string $fillHex,
        ?string $borderHex = null
    ): RichText {
        $shape = $slide->createRichTextShape()
            ->setOffsetX($x)->setOffsetY($y)
            ->setWidth($w)->setHeight($h)
            ->setInsetLeft(8)->setInsetRight(8)->setInsetTop(5)->setInsetBottom(5);

        $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color($fillHex));

        if ($borderHex !== null) {
            $shape->getBorder()
                ->setLineStyle(Border::LINE_SINGLE)
                ->setLineWidth(1)
                ->setColor(new Color($borderHex));
        }

        return $shape;
    }

    private function styleCell(
        Cell $cell,
        string $text,
        string $bgHex,
        string $textHex,
        int $fontSize,
        bool $bold,
        string $hAlign,
        string $borderHex,
        string $vAlign = Alignment::VERTICAL_CENTER
    ): void {
        $cell->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color($bgHex));
        $cell->getActiveParagraph()->getAlignment()
            ->setHorizontal($hAlign)
            ->setVertical($vAlign);
        $cell->createTextRun($text)->getFont()
            ->setName('Calibri')
            ->setBold($bold)
            ->setSize($fontSize)
            ->setColor(new Color($textHex));

        $borders = $cell->getBorders();
        foreach ([$borders->getTop(), $borders->getBottom(), $borders->getLeft(), $borders->getRight()] as $side) {
            $side->setLineStyle(Border::LINE_SINGLE)->setLineWidth(1)->setColor(new Color($borderHex));
        }
    }
}
