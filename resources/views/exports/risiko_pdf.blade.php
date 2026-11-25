<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemantauan Risiko</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }

        h1, h2, p {
            margin: 0;
        }

        .slide {
            width: 100%;
        }

        table {
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .header-box {
            background-color: #5b9bd5;
            border-radius: 7px;
            color: #ffffff;
            margin-bottom: 8px;
            padding: 10px 12px;
            page-break-inside: avoid;
        }

        .header-table, .header-fields, .columns {
            border-collapse: separate;
            width: 100%;
        }

        .header-table td, .header-fields td {
            vertical-align: top;
        }

        .header-fields td {
            padding-right: 12px;
            width: 50%;
        }

        .header-label {
            color: #eff6ff;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .header-value {
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
        }

        .bidang-cell {
            padding-left: 8px;
            text-align: right;
            vertical-align: middle !important;
            width: 18%;
        }

        .bidang-badge {
            background-color: #708238;
            border-radius: 5px;
            color: #ffffff;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            padding: 7px 10px;
            text-align: center;
        }

        .bidang-name {
            color: #f0fdf4;
            display: block;
            font-size: 10px;
            font-weight: normal;
            margin-top: 3px;
        }

        .columns {
            border-spacing: 8px 0;
            margin: 0 -8px 8px;
            table-layout: fixed;
            width: 100%;
        }

        .columns td {
            vertical-align: top;
            width: 50%;
        }

        .section {
            background-color: #ffffff;
            border: 1px solid #111827;
            border-radius: 6px;
            margin-bottom: 8px;
            page-break-inside: auto;
        }

        .section-title {
            background-color: #ff9800;
            border-radius: 5px 5px 0 0;
            color: #111827;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 9px;
            page-break-after: avoid;
        }

        .section-content {
            padding: 7px 9px;
        }

        .risk-level {
            background-color: #ffedd5;
            border: 1px solid #fed7aa;
            border-radius: 4px;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            padding: 4px 7px;
        }

        .content-list {
            margin: 0;
            padding-left: 18px;
        }

        .content-list li {
            margin-bottom: 4px;
            padding-left: 2px;
            page-break-inside: auto;
        }

        .rich-text-content p {
            margin: 0 0 4px;
        }

        .rich-text-content ol,
        .rich-text-content ul {
            margin: 4px 0;
            padding-left: 18px;
        }

        .rich-text-content ol {
            list-style-type: decimal;
        }

        .rich-text-content ul {
            list-style-type: disc;
        }

        .rich-text-content li {
            margin-bottom: 2px;
            padding-left: 2px;
        }

        .rich-text-content a {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .rich-text-content strong,
        .rich-text-content b {
            font-weight: bold;
        }

        .rich-text-content em,
        .rich-text-content i {
            font-style: italic;
        }

        .muted {
            color: #6b7280;
        }

        .recommendation-level {
            font-weight: bold;
        }

        .empty-message {
            color: #6b7280;
            font-style: italic;
        }

        .page-break {
            page-break-after: always;
        }

        .empty-report {
            background-color: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 6px;
            padding: 16px;
        }
    </style>
</head>
<body>
@forelse($reports as $report)
    @php
        $risiko = $report['risiko'];
        $progresAktual = $report['progresAktual'];
        $rekomendasi = $report['rekomendasi'];
    @endphp
    <div class="slide">
        <div class="header-box">
            <table class="header-table">
                <tr>
                    <td>
                        <table class="header-fields">
                            <tr>
                                <td>
                                    <div class="header-label">Sasaran</div>
                                    <div class="header-value">{{ $risiko->sasaran }}</div>
                                </td>
                                <td>
                                    <div class="header-label">Risiko</div>
                                    <div class="header-value">{{ $risiko->kejadian_risiko }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="header-label">Kategori Risiko</div>
                                    <div class="header-value">{{ $risiko->kategori_risiko ?? 'Belum diisi' }}</div>
                                </td>
                                <td>
                                    <div class="header-label">Bobot Area Dampak</div>
                                    <div class="header-value">{{ $risiko->bobot_area_dampak ?? 'Belum diisi' }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="bidang-cell">
                        <span class="bidang-badge">
                            {{ $risiko->bidang->kode }}
                            <span class="bidang-name">{{ $risiko->bidang->nama }}</span>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="columns">
            <tr>
                <td>
                    <div class="section">
                        <div class="section-title">Besaran/Level Risiko Aktual s.d. {{ $bulanAktual }} {{ $tahunAktual }}</div>
                        <div class="section-content">
                            <div class="risk-level">
                                {{ $risiko->besaran_risiko }} — {{ $risiko->klasifikasi['label'] }}
                            </div>
                            <p>
                                Besaran risiko aktual adalah {{ $risiko->besaran_risiko }},
                                dengan level kemungkinan {{ $risiko->level_kemungkinan }}
                                dan level dampak {{ $risiko->level_dampak }}.
                                Nilai ini mengikuti data risiko TW {{ $risiko->triwulan }}/{{ $risiko->tahun }}.
                            </p>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="section">
                        <div class="section-title">Rekomendasi Besaran Risiko Triwulan {{ $triwulanRekomendasi }} {{ $tahunRekomendasi }}</div>
                        <div class="section-content">
                            @if($rekomendasi->isNotEmpty())
                                @php($rekomendasiTriwulan = $rekomendasi->first())
                                <p>{{ $rekomendasiTriwulan->deskripsi_rekomendasi }}</p>
                                @if($rekomendasiTriwulan->rekomendasi_besaran)
                                    <div class="risk-level">
                                        {{ $rekomendasiTriwulan->rekomendasi_level_kemungkinan }}
                                        × {{ $rekomendasiTriwulan->rekomendasi_level_dampak }}
                                        = {{ $rekomendasiTriwulan->rekomendasi_besaran }}
                                        ({{ $rekomendasiTriwulan->klasifikasiRekomendasi['label'] }})
                                    </div>
                                @endif
                            @else
                                <p class="empty-message">Belum ada rekomendasi manual untuk triwulan dan tahun yang dipilih.</p>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Rencana Mitigasi</div>
            <div class="section-content">
                @if($risiko->rencana->isNotEmpty())
                    <ol class="content-list">
                        @foreach($risiko->rencana as $rencana)
                            <li>
                                <div class="rich-text-content">{!! $rencana->deskripsi_rencana !!}</div>
                                <span class="muted">({{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$rencana->status_verifikasi] ?? 'Belum divalidasi' }})</span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="empty-message">Belum ada rencana mitigasi.</p>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Progress Mitigasi Risiko s.d. {{ $bulanAktual }} {{ $tahunAktual }}</div>
            <div class="section-content">
                @if($progresAktual->isNotEmpty())
                    <ol class="content-list">
                        @foreach($progresAktual as $item)
                            <li>
                                <span class="muted">{{ $item->bulan }}/{{ $item->tahun }}:</span>
                                <div class="rich-text-content">{!! $item->deskripsi_progres !!}</div>
                                <span class="muted">({{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$item->status_verifikasi] ?? 'Belum divalidasi' }})</span>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="empty-message">Belum ada progres mitigasi sampai periode yang dipilih.</p>
                @endif
            </div>
        </div>
    </div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@empty
    <div class="empty-report">Tidak ada data risiko untuk bidang yang dipilih.</div>
@endforelse
</body>
</html>
