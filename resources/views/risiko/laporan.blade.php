@extends('layouts.app')
@section('title', 'Laporan Mitigasi')
@section('content')
    @php
        $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $statusLabel = [
            'pending' => 'Menunggu validasi',
            'approved' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ];
        $richTextClass =
            'rich-text-content break-words [&_p]:mb-1 [&_ol]:my-1.5 [&_ol]:list-decimal [&_ol]:pl-5 [&_ul]:my-1.5 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:pl-0.5 [&_a]:break-all [&_a]:text-blue-700 [&_a]:underline [&_strong]:font-semibold [&_b]:font-semibold [&_em]:italic [&_i]:italic';
    @endphp

    <div class="space-y-5">
        {{-- Header --}}
        <div class="border-b border-slate-200 pb-3">
            <h1 class="text-2xl font-bold text-[#005FAC]">Laporan Progres Mitigasi — {{ $tahun }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">Klik nomor ID risiko untuk membuka halaman detail, atau klik "Lihat
                selengkapnya" pada uraian yang panjang.</p>
        </div>

        {{-- Filter --}}
        <div class="[&>form]:mb-0">
            @include('partials.filter')
        </div>

        {{-- Form Export PDF khusus ADMIN --}}
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
                class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-bold text-slate-900">Export PDF Laporan Risiko</h2>
                <div class="flex flex-wrap items-end gap-3 text-sm">
                    <div>
                        <label for="bidang-pdf" class="mb-1 block text-xs font-medium text-slate-600">Bidang</label>
                        <select id="bidang-pdf" name="bidang" required
                            class="rounded-md border border-slate-300 px-2.5 py-1.5">
                            <option value="">Pilih bidang</option>
                            @foreach ($bidangList as $bidang)
                                <option value="{{ $bidang->kode }}" @selected($bidangPdf === $bidang->kode)>
                                    {{ $bidang->kode }} — {{ $bidang->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bulan-aktual-pdf" class="mb-1 block text-xs font-medium text-slate-600">Aktual sampai
                            bulan</label>
                        <select id="bulan-aktual-pdf" name="bulan_aktual" required
                            class="rounded-md border border-slate-300 px-2.5 py-1.5">
                            @foreach ($bulanPdf as $nomorBulan => $namaBulan)
                                <option value="{{ $namaBulan }}" @selected(request('bulan_aktual', $bulanPdf[now()->month]) === $namaBulan)>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tahun-aktual-pdf" class="mb-1 block text-xs font-medium text-slate-600">Tahun
                            aktual</label>
                        <input id="tahun-aktual-pdf" type="number" name="tahun_aktual" min="2020" max="2100"
                            value="{{ request('tahun_aktual', now()->year) }}" required
                            class="w-24 rounded-md border border-slate-300 px-2.5 py-1.5">
                    </div>
                    <div>
                        <label for="triwulan-rekomendasi-pdf" class="mb-1 block text-xs font-medium text-slate-600">Triwulan
                            rekomendasi</label>
                        <select id="triwulan-rekomendasi-pdf" name="triwulan_rekomendasi" required
                            class="rounded-md border border-slate-300 px-2.5 py-1.5">
                            @foreach ($triwulanPdf as $nomorTriwulan => $namaTriwulan)
                                <option value="{{ $namaTriwulan }}" @selected(request('triwulan_rekomendasi', $triwulanPdf[(int) ceil(now()->month / 3) - 1]) === $namaTriwulan)>
                                    Triwulan {{ $namaTriwulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tahun-rekomendasi-pdf" class="mb-1 block text-xs font-medium text-slate-600">Tahun
                            rekomendasi</label>
                        <input id="tahun-rekomendasi-pdf" type="number" name="tahun_rekomendasi" min="2020"
                            max="2100" value="{{ request('tahun_rekomendasi', now()->year) }}" required
                            class="w-24 rounded-md border border-slate-300 px-2.5 py-1.5">
                    </div>
                    <button type="submit"
                        class="rounded-md bg-[#005FAC] px-4 py-1.5 text-sm font-semibold text-white hover:bg-[#004b8a]">
                        Export PDF
                    </button>
                </div>
            </form>
        @endif

        {{-- Tabel Laporan Gabungan --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="bg-[#005FAC] text-xs font-semibold text-white">
                        <tr>
                            <th class="border border-slate-300/30 px-3 py-2.5 text-center">ID</th>
                            <th class="border border-slate-300/30 px-3 py-2.5">Bidang</th>
                            <th class="min-w-[170px] border border-slate-300/30 px-3 py-2.5">Sasaran</th>
                            <th class="min-w-[200px] border border-slate-300/30 px-3 py-2.5">Kejadian Risiko</th>
                            <th class="whitespace-nowrap border border-slate-300/30 px-3 py-2.5">Besaran</th>
                            <th class="whitespace-nowrap border border-slate-300/30 px-3 py-2.5">Kategori · Bobot</th>
                            <th class="min-w-[180px] border border-slate-300/30 px-3 py-2.5">Rekomendasi</th>
                            <th class="min-w-[220px] border border-slate-300/30 px-3 py-2.5">Rencana Mitigasi</th>
                            <th class="whitespace-nowrap border border-slate-300/30 px-3 py-2.5">Periode</th>
                            <th class="min-w-[240px] border border-slate-300/30 px-3 py-2.5">Progres</th>
                            <th class="whitespace-nowrap border border-slate-300/30 px-3 py-2.5 text-center">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($risiko as $r)
                            @php
                                $baris = [];
                                foreach ($r->rencana as $rn) {
                                    $list = $rn->progres->isEmpty() ? [null] : $rn->progres->all();
                                    foreach ($list as $idx => $p) {
                                        $baris[] = [
                                            'rn' => $rn,
                                            'p' => $p,
                                            'first' => $idx === 0,
                                            'span' => count($list),
                                        ];
                                    }
                                }
                                if (empty($baris)) {
                                    $baris[] = ['rn' => null, 'p' => null, 'first' => true, 'span' => 1];
                                }
                                $total = count($baris);
                                $klasifikasi = $r->klasifikasi;
                                $klasifikasiProyeksi = $r->proyeksi?->klasifikasiRekomendasi;
                            @endphp
                            @foreach ($baris as $i => $b)
                                <tr class="align-top hover:bg-slate-50/60">
                                    @if ($i === 0)
                                        <td class="border border-slate-200 px-3 py-2.5 text-center whitespace-nowrap"
                                            rowspan="{{ $total }}">
                                            <a href="{{ route('risiko.show', $r) }}"
                                                class="font-bold text-[#005FAC] hover:underline"
                                                title="Lihat detail Risiko #{{ $r->id }}">
                                                #{{ $r->id }}
                                            </a>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5 whitespace-nowrap font-medium text-slate-800"
                                            rowspan="{{ $total }}">
                                            {{ $r->bidang->nama }}
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5" rowspan="{{ $total }}">
                                            <div data-expandable-wrap>
                                                <div data-expandable class="line-clamp-3 text-slate-700">
                                                    {{ $r->sasaran }}</div>
                                                <button type="button" data-expand-btn
                                                    class="mt-1 hidden text-xs font-medium text-[#005FAC] hover:underline">
                                                    Lihat selengkapnya
                                                </button>
                                            </div>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5" rowspan="{{ $total }}">
                                            <div data-expandable-wrap>
                                                <div data-expandable class="line-clamp-3 font-medium text-slate-900">
                                                    <a href="{{ route('risiko.show', $r) }}"
                                                        class="hover:text-[#005FAC] hover:underline">
                                                        {{ $r->kejadian_risiko }}
                                                    </a>
                                                </div>
                                                <button type="button" data-expand-btn
                                                    class="mt-1 hidden text-xs font-medium text-[#005FAC] hover:underline">
                                                    Lihat selengkapnya
                                                </button>
                                            </div>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5 whitespace-nowrap"
                                            rowspan="{{ $total }}">
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900">
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                                    style="background-color: {{ $klasifikasi['hex'] }}"></span>
                                                {{ $r->besaran_risiko }}
                                                <span
                                                    class="text-xs font-normal text-slate-500">({{ $klasifikasi['label'] }})</span>
                                            </span>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5 whitespace-nowrap text-slate-600"
                                            rowspan="{{ $total }}">
                                            {{ $r->kategori_risiko ?? '—' }} · {{ $r->bobot_area_dampak ?? '—' }}
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2.5" rowspan="{{ $total }}">
                                            @if ($r->proyeksi)
                                                <div class="text-xs font-medium text-slate-500">TW
                                                    {{ $r->proyeksi->triwulan }}/{{ $r->proyeksi->tahun }}</div>
                                                <div data-expandable-wrap class="mt-0.5">
                                                    <div data-expandable class="line-clamp-3 text-slate-800">
                                                        {{ $r->proyeksi->deskripsi_rekomendasi }}</div>
                                                    <button type="button" data-expand-btn
                                                        class="mt-1 hidden text-xs font-medium text-[#005FAC] hover:underline">
                                                        Lihat selengkapnya
                                                    </button>
                                                </div>
                                                @if ($r->proyeksi->rekomendasi_besaran && $klasifikasiProyeksi)
                                                    <div
                                                        class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-900">
                                                        <span class="h-2 w-2 shrink-0 rounded-full"
                                                            style="background-color: {{ $klasifikasiProyeksi['hex'] }}"></span>
                                                        Proyeksi {{ $r->proyeksi->rekomendasi_besaran }}
                                                        ({{ $klasifikasiProyeksi['label'] }})
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if ($b['first'])
                                        <td class="border border-slate-200 px-3 py-2.5" rowspan="{{ $b['span'] }}">
                                            @if ($b['rn'])
                                                <div data-expandable-wrap>
                                                    <div data-expandable class="line-clamp-3 {{ $richTextClass }}">
                                                        {!! $b['rn']->deskripsi_rencana !!}
                                                    </div>
                                                    <button type="button" data-expand-btn
                                                        class="mt-1 hidden text-xs font-medium text-[#005FAC] hover:underline">
                                                        Lihat selengkapnya
                                                    </button>
                                                </div>
                                                <div class="mt-1.5 text-xs text-slate-500">
                                                    {{ $statusLabel[$b['rn']->status_verifikasi] ?? 'Belum divalidasi' }}
                                                    @if ($b['rn']->bukti_path)
                                                        · <a class="font-medium text-blue-700 hover:underline"
                                                            href="{{ route('rencana.evidence', $b['rn']) }}">Bukti</a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td class="border border-slate-200 px-3 py-2.5 whitespace-nowrap text-slate-700">
                                        {{ $b['p'] ? $bulan[$b['p']->bulan] . ' ' . $b['p']->tahun : '—' }}
                                    </td>
                                    <td class="border border-slate-200 px-3 py-2.5">
                                        @if ($b['p'])
                                            <div data-expandable-wrap>
                                                <div data-expandable class="line-clamp-3 {{ $richTextClass }}">
                                                    {!! $b['p']->deskripsi_progres !!}
                                                </div>
                                                <button type="button" data-expand-btn
                                                    class="mt-1 hidden text-xs font-medium text-[#005FAC] hover:underline">
                                                    Lihat selengkapnya
                                                </button>
                                            </div>
                                            <div class="mt-1.5 text-xs text-slate-500">
                                                {{ $statusLabel[$b['p']->status_verifikasi] ?? 'Belum divalidasi' }}
                                            </div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="border border-slate-200 px-3 py-2.5 text-center whitespace-nowrap">
                                        @if ($b['p'] && ($b['p']->bukti_path || $b['p']->file_bukti))
                                            <a class="font-medium text-blue-700 hover:underline"
                                                href="{{ route('progres.evidence', $b['p']) }}">Lihat</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-slate-500">Tidak ada data laporan
                                    untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function() {
            document.querySelectorAll('[data-expandable-wrap]').forEach((wrap) => {
                const content = wrap.querySelector('[data-expandable]');
                const btn = wrap.querySelector('[data-expand-btn]');
                if (!content || !btn) return;

                // Tampilkan tombol hanya jika teks benar-benar terpotong (lebih dari 3 baris)
                if (content.scrollHeight > content.clientHeight + 2) {
                    btn.classList.remove('hidden');
                }

                btn.addEventListener('click', () => {
                    const expanded = !content.classList.contains('line-clamp-3');
                    content.classList.toggle('line-clamp-3', expanded);
                    btn.textContent = expanded ? 'Lihat selengkapnya' : 'Tutup';
                });
            });
        })();
    </script>
@endsection
