<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Peta Risiko Kanwil DJPb Sumut {{ $tahun }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
        }

        table.layout {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        table.layout>tbody>tr>td {
            vertical-align: top;
        }

        .left-col {
            padding-right: 12px;
            width: 46%;
        }

        .right-col {
            width: 54%;
        }

        .main-title {
            font-size: 17px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }

        /* Matriks 5x5 */
        table.matriks {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        table.matriks th,
        table.matriks td {
            border: 1px solid #374151;
            padding: 2px 3px;
        }

        .matriks-top {
            font-size: 8px;
            font-weight: bold;
            text-align: left;
        }

        .matriks-hdr {
            background-color: #f8fafc;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
        }

        .matriks-subhdr {
            background-color: #ffffff;
            font-size: 6.5px;
            font-weight: normal;
            text-align: left;
        }

        .axis-y {
            background-color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            width: 18px;
        }

        .axis-y-label {
            font-size: 6.5px;
            text-align: left;
            width: 68px;
        }

        .axis-y-num {
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
            width: 14px;
        }

        .cell-box {
            height: 44px;
            text-align: left;
            vertical-align: top;
        }

        .cell-score {
            font-size: 15px;
            font-weight: bold;
            line-height: 1.05;
        }

        .cell-ids {
            margin-top: 2px;
        }

        .risk-chip {
            background-color: #ffffff;
            border: 1px solid #1f2937;
            border-radius: 3px;
            color: #111827;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            margin: 1px 1px 0 0;
            padding: 0 3px;
        }

        .risk-chip-upside {
            border-radius: 9px;
        }

        /* Legenda */
        table.legenda {
            border-collapse: collapse;
            margin-top: 10px;
            width: 72%;
        }

        table.legenda th,
        table.legenda td {
            border: 1px solid #374151;
            font-size: 8.5px;
            padding: 3px 6px;
            text-align: center;
        }

        table.legenda th {
            background-color: #ffffff;
            font-weight: bold;
        }

        /* Tabel Daftar Kejadian Risiko */
        table.daftar {
            border-collapse: collapse;
            width: 100%;
        }

        table.daftar th {
            background-color: #4bacc6;
            border: 1.5px solid #ffffff;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: bold;
            padding: 6px 6px;
            text-align: center;
        }

        table.daftar td {
            border: 1.5px solid #ffffff;
            font-size: 10px;
            padding: 6px 7px;
            vertical-align: middle;
        }

        .col-no {
            font-weight: bold;
            text-align: center;
            width: 7%;
        }

        .col-kejadian {
            line-height: 1.25;
            text-align: left;
            width: 73%;
        }

        .col-skor {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            width: 10%;
        }
    </style>
</head>

<body>
    @php
        $labelK = [
            5 => 'Hampir Pasti Terjadi',
            4 => 'Sering Terjadi',
            3 => 'Kadang Terjadi',
            2 => 'Jarang Terjadi',
            1 => 'Hampir Tidak Terjadi',
        ];
        $labelD = [
            1 => '1 - Tidak Signifikan',
            2 => '2 - Minor',
            3 => '3 - Moderat',
            4 => '4 - Signifikan',
            5 => '5 - Sangat Signifikan',
        ];
        $warnaMatriks = [
            'biru' => '#5CC8FF',
            'hijau' => '#1EEB77',
            'kuning' => '#FFFF00',
            'oranye' => '#F79646',
            'merah' => '#F24C4C',
        ];
        $warnaDaftar = [
            'merah' => 'background-color: #FF0000; color: #111827;',
            'oranye' => 'background-color: #FFC000; color: #111827;',
            'kuning' => 'background-color: #FFFF00; color: #111827;',
            'hijau' => 'background-color: #92D050; color: #111827;',
            'biru' => 'background-color: #5B9BD5; color: #111827;',
        ];
    @endphp

    <table class="layout">
        <tr>
            {{-- KOLOM KIRI: Judul + Matriks 5x5 + Legenda --}}
            <td class="left-col">
                <h1 class="main-title">Peta Risiko Kanwil DJPb Sumut {{ $tahun }}</h1>

                <table class="matriks">
                    <tr>
                        <td colspan="8" class="matriks-top">2. Peta Risiko</td>
                    </tr>
                    <tr>
                        <th rowspan="2" colspan="3" class="matriks-hdr">Matriks Analisis<br>Risiko</th>
                        <th colspan="5" class="matriks-hdr">Level Dampak</th>
                    </tr>
                    <tr>
                        @for ($d = 1; $d <= 5; $d++)
                            <th class="matriks-subhdr">{{ $labelD[$d] }}</th>
                        @endfor
                    </tr>
                    @for ($k = 5; $k >= 1; $k--)
                        <tr>
                            @if ($k === 5)
                                <td rowspan="5" class="axis-y">Level<br>Kemung-<br>kinan</td>
                            @endif
                            <td class="axis-y-num">{{ $k }}</td>
                            <td class="axis-y-label">{{ $labelK[$k] }}</td>
                            @for ($d = 1; $d <= 5; $d++)
                                @php
                                    $besaran = \App\Models\Risiko::hitungBesaran($k, $d);
                                    $kode = \App\Models\Risiko::klasifikasi($besaran)['kode'];
                                    $bg = $warnaMatriks[$kode] ?? '#FFFFFF';
                                @endphp
                                <td class="cell-box" style="background-color: {{ $bg }};">
                                    <div class="cell-score">{{ $besaran }}</div>
                                    @if ($matriksSaatIni[$k][$d]->isNotEmpty())
                                        <div class="cell-ids">
                                            @foreach ($matriksSaatIni[$k][$d] as $r)
                                                <span
                                                    class="risk-chip {{ $r->jenis_risiko === 'upside' ? 'risk-chip-upside' : '' }}">{{ $r->id }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </table>

                <table class="legenda">
                    <thead>
                        <tr>
                            <th>Level Risiko</th>
                            <th>Besaran Risiko</th>
                            <th>Warna</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sangat Tinggi (5)</td>
                            <td>20 - 25</td>
                            <td style="background-color: #FF0000; font-weight: bold;">Merah</td>
                        </tr>
                        <tr>
                            <td>Tinggi (4)</td>
                            <td>16 - 19</td>
                            <td style="background-color: #FFC000; font-weight: bold;">Oranye</td>
                        </tr>
                        <tr>
                            <td>Sedang (3)</td>
                            <td>12 - 15</td>
                            <td style="background-color: #FFFF00; font-weight: bold;">Kuning</td>
                        </tr>
                        <tr>
                            <td>Rendah (2)</td>
                            <td>6 - 11</td>
                            <td style="background-color: #00B050; font-weight: bold;">Hijau</td>
                        </tr>
                        <tr>
                            <td>Sangat Rendah (1)</td>
                            <td>1 - 5</td>
                            <td style="background-color: #5B9BD5; font-weight: bold;">Biru</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            {{-- KOLOM KANAN: Tabel 10 Daftar Kejadian Risiko --}}
            <td class="right-col">
                <table class="daftar">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-kejadian" style="text-align: center;">Kejadian Risiko</th>
                            <th class="col-skor">{{ $labelAwalTahun }}</th>
                            <th class="col-skor">{{ $labelKuartal }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarKejadian as $item)
                            @php
                                $kodeAwal = $item['klasifikasi_awal']['kode'] ?? null;
                                $kodeKuartal = $item['klasifikasi_kuartal']['kode'] ?? null;
                                $styleBaris = $kodeAwal
                                    ? $warnaDaftar[$kodeAwal] ?? ''
                                    : ($kodeKuartal
                                        ? $warnaDaftar[$kodeKuartal] ?? ''
                                        : 'background-color: #F8FAFC; color: #111827;');
                                $styleAwal = $kodeAwal
                                    ? $warnaDaftar[$kodeAwal] ?? ''
                                    : 'background-color: #F8FAFC; color: #475569;';
                                $styleKuartal = $kodeKuartal
                                    ? $warnaDaftar[$kodeKuartal] ?? ''
                                    : 'background-color: #F8FAFC; color: #475569;';
                            @endphp
                            <tr>
                                <td class="col-no" style="{{ $styleBaris }}">{{ $item['no'] }}</td>
                                <td class="col-kejadian" style="{{ $styleBaris }}">{{ $item['kejadian_risiko'] }}
                                </td>
                                <td class="col-skor" style="{{ $styleAwal }}">{{ $item['nilai_awal'] ?? '—' }}</td>
                                <td class="col-skor" style="{{ $styleKuartal }}">{{ $item['nilai_kuartal'] ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
