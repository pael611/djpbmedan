@extends('layouts.app')
@section('title', 'Laporan Mitigasi')
@section('content')
    @php $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; @endphp
    <h1 class="text-2xl font-bold mb-3">Laporan Progres Mitigasi — {{ $tahun }}</h1>

    @include('partials.filter')

    @if (auth()->user()->role === 'ADMIN')
        @php
            $bulanPdf = [
                1 => 'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];
            $triwulanPdf = ['I', 'II', 'III', 'IV'];
            $bidangPdf = request('bidang', request('bidang_id', ''));
        @endphp
        <form method="GET" action="{{ route('risiko.export-pdf') }}"
            class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap items-end gap-3">
            <div>
                <h1 class="text-lg font-bold mb-2">Export PDF</h1>
                <label for="bidang-pdf" class="block text-xs mb-1">Bidang</label>
                <select id="bidang-pdf" name="bidang" required class="border rounded px-2 py-1">
                    <option value="">Pilih bidang</option>
                    @foreach ($bidangList as $bidang)
                        <option value="{{ $bidang->kode }}" @selected($bidangPdf === $bidang->kode)>
                            {{ $bidang->kode }} — {{ $bidang->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bulan-aktual-pdf" class="block text-xs mb-1">Aktual sampai bulan</label>
                <select id="bulan-aktual-pdf" name="bulan_aktual" required class="border rounded px-2 py-1">
                    @foreach ($bulanPdf as $nomorBulan => $namaBulan)
                        <option value="{{ $namaBulan }}" @selected(request('bulan_aktual', $bulanPdf[now()->month]) === $namaBulan)>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tahun-aktual-pdf" class="block text-xs mb-1">Tahun aktual</label>
                <input id="tahun-aktual-pdf" type="number" name="tahun_aktual" min="2020" max="2100"
                    value="{{ request('tahun_aktual', now()->year) }}" required class="border rounded px-2 py-1 w-24">
            </div>
            <div>
                <label for="triwulan-rekomendasi-pdf" class="block text-xs mb-1">Triwulan rekomendasi</label>
                <select id="triwulan-rekomendasi-pdf" name="triwulan_rekomendasi" required class="border rounded px-2 py-1">
                    @foreach ($triwulanPdf as $nomorTriwulan => $namaTriwulan)
                        <option value="{{ $namaTriwulan }}" @selected(request('triwulan_rekomendasi', $triwulanPdf[(int) ceil(now()->month / 3) - 1]) === $namaTriwulan)>
                            Triwulan {{ $namaTriwulan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tahun-rekomendasi-pdf" class="block text-xs mb-1">Tahun rekomendasi</label>
                <input id="tahun-rekomendasi-pdf" type="number" name="tahun_rekomendasi" min="2020" max="2100"
                    value="{{ request('tahun_rekomendasi', now()->year) }}" required class="border rounded px-2 py-1 w-24">
            </div>
            <button class="bg-blue-700 text-white rounded px-4 py-1.5 hover:bg-blue-800">Export PDF</button>
        </form>
    @endif

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead class="bg-slate-800 text-white text-left">
                <tr>
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Bidang</th>
                    <th class="p-2 border">Sasaran</th>
                    <th class="p-2 border">Kejadian Risiko</th>
                    <th class="p-2 border">Besaran</th>
                    <th class="p-2 border">Kategori Risiko</th>
                    <th class="p-2 border">Bobot Area Dampak</th>
                    <th class="p-2 border">Rekomendasi</th>
                    <th class="p-2 border">Rencana Mitigasi</th>
                    <th class="p-2 border">Periode </th>
                    <th class="p-2 border">Progres</th>
                    <th class="p-2 border">Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($risiko as $r)
                    @php
                        // Susun baris datar: satu baris per progres (atau 1 baris kosong bila belum ada)
                        $baris = [];
                        foreach ($r->rencana as $rn) {
                            $list = $rn->progres->isEmpty() ? [null] : $rn->progres->all();
                            foreach ($list as $i => $p) {
                                $baris[] = ['rn' => $rn, 'p' => $p, 'first' => $i === 0, 'span' => count($list)];
                            }
                        }
                        if (empty($baris)) {
                            $baris[] = ['rn' => null, 'p' => null, 'first' => true, 'span' => 1];
                        }
                        $total = count($baris);
                    @endphp
                    @foreach ($baris as $i => $b)
                        <tr class="align-top">
                            @if ($i === 0)
                                <td class="p-2 border font-bold" rowspan="{{ $total }}">{{ $r->id }}</td>
                                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->bidang->nama }}</td>
                                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->sasaran }}</td>
                                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->kejadian_risiko }}</td>
                                <td class="p-2 border" rowspan="{{ $total }}">@include('partials.badge', ['besaran' => $r->besaran_risiko])</td>
                                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->kategori_risiko ?? '—' }}</td>
                                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->bobot_area_dampak ?? '—' }}
                                </td>
                                <td class="p-2 border" rowspan="{{ $total }}">
                                    @if ($r->proyeksi)
                                        <div class="text-xs text-slate-500">TW
                                            {{ $r->proyeksi->triwulan }}/{{ $r->proyeksi->tahun }}</div>
                                        <div>{{ $r->proyeksi->deskripsi_rekomendasi }}</div>
                                        @if ($r->proyeksi->rekomendasi_besaran)
                                            @include('partials.badge', [
                                                'besaran' => $r->proyeksi->rekomendasi_besaran,
                                            ])
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                            @if ($b['first'])
                                <td class="p-2 border" rowspan="{{ $b['span'] }}">
                                    <div
                                        class="rich-text-content break-words [&_p]:mb-1 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-1 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic">
                                        {!! $b['rn']->deskripsi_rencana ?? '—' !!}</div>
                                    @if ($b['rn'])
                                        <div class="mt-1 text-xs">
                                            {{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$b['rn']->status_verifikasi] ?? 'Belum divalidasi' }}
                                            @if ($b['rn']->bukti_path)
                                                · <a class="text-blue-700 hover:underline"
                                                    href="{{ route('rencana.evidence', $b['rn']) }}">Bukti</a>
                                            @endif
                                        </div>
                                    @endif
                            @endif
                            <td class="p-2 border whitespace-nowrap">
                                {{ $b['p'] ? $bulan[$b['p']->bulan] . ' ' . $b['p']->tahun : '—' }}</td>
                            <td class="p-2 border">
                                <div
                                    class="rich-text-content break-words [&_p]:mb-1 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-1 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic">
                                    {!! $b['p']->deskripsi_progres ?? '—' !!}</div>
                                @if ($b['p'])
                                    <div class="mt-1 text-xs">
                                        {{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$b['p']->status_verifikasi] ?? 'Belum divalidasi' }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-2 border">
                                @if ($b['p'] && ($b['p']->bukti_path || $b['p']->file_bukti))
                                    <a class="text-blue-700 hover:underline"
                                        href="{{ route('progres.evidence', $b['p']) }}">Lihat</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="12" class="p-4 text-center text-slate-500">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
