<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manual Indikator Risiko Utama (IRU)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 16mm;
        }

        body {
            color: #111827;
            font-family: "DejaVu Serif", Georgia, serif;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }

        .page {
            width: 100%;
        }

        .page-break {
            page-break-after: always;
        }

        h1.doc-title {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 14px 0;
            text-align: center;
        }

        table.meta-table {
            border-collapse: collapse;
            margin-bottom: 10px;
            width: 100%;
        }

        table.meta-table td {
            padding: 3.5px 4px;
            vertical-align: top;
        }

        table.meta-table td.label-col {
            width: 28%;
        }

        table.meta-table td.colon-col {
            text-align: center;
            width: 3%;
        }

        table.meta-table td.value-col {
            width: 69%;
        }

        .hint {
            color: #4b5563;
            font-style: italic;
        }

        .option-item {
            display: inline-block;
            margin-right: 16px;
        }

        .check-mark {
            font-family: "DejaVu Sans", sans-serif;
            font-weight: bold;
        }

        table.data-table {
            border-collapse: collapse;
            margin-top: 6px;
            width: 100%;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #111827;
            font-size: 10px;
            padding: 5px 4px;
            text-align: center;
        }

        table.data-table th {
            background-color: #d9d9d9;
            font-weight: bold;
        }

        table.data-table td.periode-col {
            text-align: left;
            width: 14%;
        }

        .footnote {
            font-size: 9.5px;
            line-height: 1.4;
            margin-top: 6px;
        }
    </style>
</head>
<body>
@forelse($items as $entry)
    @php
        $risiko = $entry['risiko'];
        $indikator = $entry['indikator'];
        $riwayat = $entry['riwayat'];
        $konsolidasi = $indikator->jenis_konsolidasi ?? 'tidak_ada';
        $polarisasi = $indikator->polarisasi;
        $periode = $indikator->periode_pemantauan;
    @endphp
    <div class="page">
        <h1 class="doc-title">Manual Indikator Risiko Utama (IRU)</h1>

        <table class="meta-table">
            <tr>
                <td class="label-col">Sasaran Organisasi</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $risiko->sasaran }}</td>
            </tr>
            <tr>
                <td class="label-col">Risiko</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $risiko->kejadian_risiko }}</td>
            </tr>
            <tr>
                <td class="label-col">IRU</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->nama_indikator }}</td>
            </tr>
            <tr>
                <td class="label-col">Deskripsi IRU</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->definisi ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label-col">Formula</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->formula ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label-col">Satuan Pengukuran</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->satuan }}</td>
            </tr>
            <tr>
                <td class="label-col">Jenis konsolidasi periode</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">(<span class="check-mark">{{ $konsolidasi === 'tidak_ada' ? '✓' : ' ' }}</span>) Tidak ada</span>
                    <span class="option-item">(<span class="check-mark">{{ $konsolidasi === 'tlk' ? '✓' : ' ' }}</span>) Take last Known Value (TLK)</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Polarisasi</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">(<span class="check-mark">{{ $polarisasi === 'maximize' ? '✓' : ' ' }}</span>) Maximize</span>
                    <span class="option-item">(<span class="check-mark">{{ $polarisasi === 'minimize' ? '✓' : ' ' }}</span>) Minimize</span>
                    <span class="option-item">(<span class="check-mark">{{ $polarisasi === 'stabilize' ? '✓' : ' ' }}</span>) Stabilize</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Unit Penanggung Jawab</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->penanggung_jawab ?: ($risiko->bidang->nama ?? '—') }}</td>
            </tr>
            <tr>
                <td class="label-col">Unit Penyedia Data</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->penyedia_data ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label-col">Sumber Data</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $indikator->sumber_data ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label-col">Periode Pelaporan</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">(<span class="check-mark">{{ $periode === 'bulanan' ? '✓' : ' ' }}</span>) Bulanan</span>
                    <span class="option-item">(<span class="check-mark">{{ $periode === 'triwulanan' ? '✓' : ' ' }}</span>) Triwulanan</span>
                    <span class="option-item">(<span class="check-mark">{{ $periode === 'semesteran' ? '✓' : ' ' }}</span>) Semesteran</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Tabel Data</td>
                <td class="colon-col">:</td>
                <td class="value-col"></td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2">Periode</th>
                    <th colspan="4">Y-2 ({{ $tahun - 2 }})</th>
                    <th colspan="4">Y-1 ({{ $tahun - 1 }})</th>
                    <th colspan="3">Y ({{ $tahun }})</th>
                </tr>
                <tr>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                    <th>Aktual</th>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                    <th>Aktual</th>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                </tr>
            </thead>
            <tbody>
                @foreach($riwayat as $baris)
                    <tr>
                        <td class="periode-col">{{ $baris['label'] }}</td>
                        <td>{{ $baris['y_minus_2']['bm'] }}</td>
                        <td>{{ $baris['y_minus_2']['ba'] }}</td>
                        <td>{{ $baris['y_minus_2']['bb'] }}</td>
                        <td>{{ $baris['y_minus_2']['aktual'] }}</td>
                        <td>{{ $baris['y_minus_1']['bm'] }}</td>
                        <td>{{ $baris['y_minus_1']['ba'] }}</td>
                        <td>{{ $baris['y_minus_1']['bb'] }}</td>
                        <td>{{ $baris['y_minus_1']['aktual'] }}</td>
                        <td>{{ $baris['y']['bm'] }}</td>
                        <td>{{ $baris['y']['ba'] }}</td>
                        <td>{{ $baris['y']['bb'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footnote">
            Keterangan: Batas Aman: BM; Batas Atas: BA; Batas Bawah: BB. Dalam hal IRU stabilize, BM diisi dengan batas aman (A) dan batas aman (B).
        </p>
    </div>

    @if(! $loop->last)
        <div class="page-break"></div>
    @endif
@empty
    <div class="page">
        <h1 class="doc-title">Manual Indikator Risiko Utama (IRU)</h1>

        <table class="meta-table">
            <tr>
                <td class="label-col">Sasaran Organisasi</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi dengan nama sasaran organisasi)</span></td>
            </tr>
            <tr>
                <td class="label-col">Risiko</td>
                <td class="colon-col">:</td>
                <td class="value-col">{{ $kejadianDipilih }}</td>
            </tr>
            <tr>
                <td class="label-col">IRU</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi dengan nama IRU)</span></td>
            </tr>
            <tr>
                <td class="label-col">Deskripsi IRU</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi definisi IRU yang meliputi pengertian dan ruang lingkup)</span></td>
            </tr>
            <tr>
                <td class="label-col">Formula</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi formula IRU)</span></td>
            </tr>
            <tr>
                <td class="label-col">Satuan Pengukuran</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi unit pengukuran yang digunakan untuk menunjukkan kuantitas IRU, misal %, Rp, USD, kali, buah, orang)</span></td>
            </tr>
            <tr>
                <td class="label-col">Jenis konsolidasi periode</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">( ) Tidak ada</span>
                    <span class="option-item">( ) Take last Known Value (TLK)</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Polarisasi</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">( ) Maximize</span>
                    <span class="option-item">( ) Minimize</span>
                    <span class="option-item">( ) Stabilize</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Unit Penanggung Jawab</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi unit/Individu pada level dibawahnya yang bertanggung jawab terhadap pencapaian IRU tersebut)</span></td>
            </tr>
            <tr>
                <td class="label-col">Unit Penyedia Data</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi unit/Individu yang bertanggungjawab terhadap penyedia data)</span></td>
            </tr>
            <tr>
                <td class="label-col">Sumber Data</td>
                <td class="colon-col">:</td>
                <td class="value-col"><span class="hint">(diisi dengan nama dokumen, aplikasi atau sumber lainnya yang memuat informasi tentang realisasi IRU tersebut)</span></td>
            </tr>
            <tr>
                <td class="label-col">Periode Pelaporan</td>
                <td class="colon-col">:</td>
                <td class="value-col">
                    <span class="option-item">( ) Bulanan</span>
                    <span class="option-item">( ) Triwulanan</span>
                    <span class="option-item">( ) Semesteran</span>
                </td>
            </tr>
            <tr>
                <td class="label-col">Tabel Data</td>
                <td class="colon-col">:</td>
                <td class="value-col"></td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2">Periode</th>
                    <th colspan="4">Y-2 ({{ $tahun - 2 }})</th>
                    <th colspan="4">Y-1 ({{ $tahun - 1 }})</th>
                    <th colspan="3">Y ({{ $tahun }})</th>
                </tr>
                <tr>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                    <th>Aktual</th>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                    <th>Aktual</th>
                    <th>BM</th>
                    <th>BA</th>
                    <th>BB</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'] as $tw)
                    <tr>
                        <td class="periode-col">{{ $tw }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footnote">
            Keterangan: Batas Aman: BM; Batas Atas: BA; Batas Bawah: BB. Dalam hal IRU stabilize, BM diisi dengan batas aman (A) dan batas aman (B).
        </p>
    </div>
@endforelse
</body>
</html>
