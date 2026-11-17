@extends('layouts.app')
@section('title', 'Laporan Mitigasi')
@section('content')
@php $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; @endphp
<h1 class="text-2xl font-bold mb-3">Laporan Progres Mitigasi — {{ $tahun }}</h1>

@include('partials.filter')

@if(auth()->user()->role === 'ADMIN')
    @php
        $bulanPdf = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $triwulanPdf = ['I', 'II', 'III', 'IV'];
        $bidangPdf = request('bidang', request('bidang_id', ''));
    @endphp
    <form method="GET" action="{{ route('risiko.export-pdf') }}" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="bidang-pdf" class="block text-xs mb-1">Bidang</label>
            <select id="bidang-pdf" name="bidang" required class="border rounded px-2 py-1">
                <option value="">Pilih bidang</option>
                @foreach($bidangList as $bidang)
                    <option value="{{ $bidang->kode }}" @selected($bidangPdf === $bidang->kode)>
                        {{ $bidang->kode }} — {{ $bidang->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="bulan-aktual-pdf" class="block text-xs mb-1">Aktual sampai bulan</label>
            <select id="bulan-aktual-pdf" name="bulan_aktual" required class="border rounded px-2 py-1">
                @foreach($bulanPdf as $nomorBulan => $namaBulan)
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
                @foreach($triwulanPdf as $nomorTriwulan => $namaTriwulan)
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
                foreach ($list as $i => $p) { $baris[] = ['rn' => $rn, 'p' => $p, 'first' => $i === 0, 'span' => count($list)]; }
            }
            if (empty($baris)) { $baris[] = ['rn' => null, 'p' => null, 'first' => true, 'span' => 1]; }
            $total = count($baris);
        @endphp
        @foreach($baris as $i => $b)
        <tr class="align-top">
            @if($i === 0)
                <td class="p-2 border font-bold" rowspan="{{ $total }}">{{ $r->id }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->bidang->nama }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->sasaran }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->kejadian_risiko }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">@include('partials.badge', ['besaran' => $r->besaran_risiko])</td>
                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->kategori_risiko ?? '—' }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">{{ $r->bobot_area_dampak ?? '—' }}</td>
                <td class="p-2 border" rowspan="{{ $total }}">
                    @if($r->proyeksi)
                        <div class="text-xs text-slate-500">TW {{ $r->proyeksi->triwulan }}/{{ $r->proyeksi->tahun }}</div>
                        <div>{{ $r->proyeksi->deskripsi_rekomendasi }}</div>
                        @if($r->proyeksi->rekomendasi_besaran)
                            @include('partials.badge', ['besaran' => $r->proyeksi->rekomendasi_besaran])
                        @endif
                    @else
                        —
                    @endif
                </td>
            @endif
            @if($b['first'])
                <td class="p-2 border" rowspan="{{ $b['span'] }}">{{ $b['rn']->deskripsi_rencana ?? '—' }}</td>
            @endif
            <td class="p-2 border whitespace-nowrap">{{ $b['p'] ? $bulan[$b['p']->bulan].' '.$b['p']->tahun : '—' }}</td>
            <td class="p-2 border">{{ $b['p']->deskripsi_progres ?? '—' }}</td>
            <td class="p-2 border">
                @if($b['p'] && $b['p']->file_bukti)<a class="text-blue-700 hover:underline" target="_blank" href="{{ Storage::url($b['p']->file_bukti) }}">Lihat</a>@else — @endif
            </td>
        </tr>
        @endforeach
    @empty
        <tr><td colspan="12" class="p-4 text-center text-slate-500">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
