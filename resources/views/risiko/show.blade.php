@extends('layouts.app')
@section('title', 'Risiko #'.$risiko->id)
@section('content')
@php $bulan = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; @endphp

<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
    <h1 class="text-2xl font-bold">Risiko ID #{{ $risiko->id }} <span class="text-base font-normal text-slate-500">— {{ $risiko->bidang->nama }}, TW {{ $risiko->triwulan }}/{{ $risiko->tahun }}</span></h1>
    <div class="flex gap-2">
        <a href="{{ route('risiko.edit', $risiko) }}" class="border rounded px-3 py-1.5 text-sm bg-white">{{ auth()->user()->isPrivileged() ? 'Ubah' : 'Ajukan ubah' }}</a>
        <form method="POST" action="{{ route('risiko.destroy', $risiko) }}" onsubmit="return confirm('Hapus kejadian risiko beserta mitigasinya?')">
            @csrf @method('DELETE')
            <button class="border border-red-300 text-red-700 rounded px-3 py-1.5 text-sm bg-white">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan hapus' }}</button>
        </form>
    </div>
</div>
@unless(auth()->user()->isPrivileged())
    <p class="mb-4 rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">Pengajuan edit dan hapus akan diterapkan setelah disetujui Admin atau SKKI.</p>
@endunless

<div class="bg-white rounded-lg shadow p-5 mb-6 grid md:grid-cols-5 gap-4 text-sm">
    <div><div class="text-xs text-slate-500">Sasaran</div>{{ $risiko->sasaran }}</div>
    <div><div class="text-xs text-slate-500">Kejadian Risiko</div>{{ $risiko->kejadian_risiko }}</div>
    <div>
        <div class="text-xs text-slate-500">Besaran Risiko</div>
        {{ $risiko->level_kemungkinan }} × {{ $risiko->level_dampak }} =
        @include('partials.badge', ['besaran' => $risiko->besaran_risiko])
    </div>
    <div><div class="text-xs text-slate-500">Kategori Risiko</div>{{ $risiko->kategori_risiko ?? '—' }}</div>
    <div><div class="text-xs text-slate-500">Bobot Area Dampak</div>{{ $risiko->bobot_area_dampak ?? '—' }}</div>
</div>

<section class="bg-white rounded-lg shadow p-5 mb-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold">Evaluasi Risiko Lanjutan</h2>
            <p class="text-sm text-slate-500">Tinjau aktual IRU dan kejadian LED untuk menentukan evaluasi LK Triwulan {{ $triwulanEvaluasi }}/{{ $tahunEvaluasi }}.</p>
        </div>
        <form method="GET" action="{{ route('risiko.show', $risiko) }}" class="flex flex-wrap items-end gap-2">
            <label class="text-xs">
                <span class="block mb-1">Triwulan</span>
                <select name="triwulan" class="border rounded px-2 py-1.5" onchange="this.form.submit()">
                    @foreach([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $nomor => $nama)
                        <option value="{{ $nomor }}" @selected($triwulanEvaluasi === $nomor)>Triwulan {{ $nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs">
                <span class="block mb-1">Tahun</span>
                <input type="number" name="tahun" min="2020" max="2100" value="{{ $tahunEvaluasi }}" onchange="this.form.submit()" class="border rounded px-2 py-1.5 w-28">
            </label>
            <noscript><button class="border rounded px-3 py-1.5 text-sm">Tampilkan</button></noscript>
        </form>
    </div>

    <div class="grid md:grid-cols-2 gap-3 mt-4 text-sm">
        <div class="rounded-md border border-sky-200 bg-sky-50 p-3">
            <div class="text-xs text-slate-600">Risiko awal tahun (P<sub>y</sub>)</div>
            <div class="font-semibold mt-1">LK {{ $risiko->level_kemungkinan }} · LD {{ $risiko->level_dampak }}</div>
            <div class="mt-1">Besaran {{ $risiko->besaran_risiko }} — {{ $risiko->klasifikasi['label'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-600">Acuan LK sebelum evaluasi ini</div>
            @if($rekomendasiSebelumnya)
                <div class="font-semibold mt-1">Triwulan {{ $rekomendasiSebelumnya->triwulan }}/{{ $rekomendasiSebelumnya->tahun }}</div>
                <div>LK {{ $levelKemungkinanSebelumnya }}{{ $rekomendasiSebelumnya->rekomendasi_besaran ? ' · besaran residual '.$rekomendasiSebelumnya->rekomendasi_besaran : '' }}</div>
            @else
                <div class="font-semibold mt-1">Belum ada evaluasi sebelumnya</div>
                <div>Perbandingan dimulai dari LK risiko awal tahun.</div>
            @endif
        </div>
    </div>

    @if($adaPeringatanKemungkinan)
        <div role="alert" class="mt-4 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
            <h3 class="font-semibold">Perlu meninjau kembali estimasi Level Kemungkinan (LK)</h3>
            @if($indikatorWaspada->isNotEmpty())
                <p class="mt-1">Nilai IRU terbaru berstatus Waspada/Awas: {{ $indikatorWaspada->pluck('nama_indikator')->join(', ') }}.</p>
            @endif
            @if($kejadianLedTerverifikasi->isNotEmpty())
                <p class="mt-1">Terdapat {{ $kejadianLedTerverifikasi->count() }} kejadian LED terverifikasi pada triwulan ini.</p>
            @endif
            <p class="mt-2">
                Saran analisis: pertimbangkan LK {{ $levelKemungkinanSebelumnya }} → {{ $saranLevelKemungkinan }} berdasarkan indikator dan/atau kejadian.
                @if($levelKemungkinanSebelumnya >= 5)
                    LK sudah berada pada tingkat tertinggi; lakukan penilaian ulang atas penyebab dan data historis.
                @endif
            </p>
            <p class="mt-1 text-xs">Saran tidak mengubah level secara otomatis. UPR tetap menetapkan LK berdasarkan penyebab risiko, data historis, dan hasil pemantauan.</p>
        </div>
    @else
        <p class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
            Tidak ada IRU berstatus Waspada/Awas atau kejadian LED tercatat pada triwulan ini.
        </p>
    @endif
    @if($indikatorTanpaDataTerkini->isNotEmpty())
        <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950">
            Belum ada nilai aktual IRU dalam enam bulan sampai periode ini:
            {{ $indikatorTanpaDataTerkini->pluck('nama_indikator')->join(', ') }}.
            Lengkapi pemantauan sesuai frekuensi indikator; ini tidak otomatis menaikkan LK.
        </p>
    @endif
</section>

<section class="bg-white rounded-lg shadow p-5 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <h2 class="text-lg font-semibold">Indikator Risiko Utama (IRU)</h2>
            <p class="text-sm text-slate-500">BM = Batas Aman, BA = Batas Atas, BB = Batas Bawah. Status memakai nilai terbaru dalam enam bulan sampai triwulan evaluasi.</p>
        </div>
        @if(in_array($risiko->kejadian_risiko, \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO, true))
            <a href="{{ route('risiko.manual-iru-pdf', ['kejadian_risiko' => $risiko->kejadian_risiko, 'tahun' => $tahunEvaluasi, 'bidang_id' => $risiko->bidang_id]) }}"
               class="rounded bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">
                Cetak PDF Manual IRU (Kejadian Ini)
            </a>
        @endif
        <details class="w-full mt-2">
            <summary class="cursor-pointer text-sm font-medium text-sky-800">+ Tambah indikator IRU</summary>
            <form method="POST" action="{{ route('indikator-iru.store', $risiko) }}" data-iru-form class="grid md:grid-cols-3 gap-3 mt-3 rounded-md border p-3 text-sm">
                @csrf
                <label>Nama indikator<input name="nama_indikator" required maxlength="150" value="{{ old('nama_indikator') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Satuan<input name="satuan" required maxlength="50" value="{{ old('satuan') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Jenis konsolidasi periode
                    <select name="jenis_konsolidasi" class="mt-1 border rounded px-2 py-1.5 w-full">
                        @foreach(\App\Models\IndikatorRisiko::JENIS_KONSOLIDASI as $kodeKonsolidasi => $labelKonsolidasi)
                            <option value="{{ $kodeKonsolidasi }}" @selected(old('jenis_konsolidasi', 'tidak_ada') === $kodeKonsolidasi)>{{ $labelKonsolidasi }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="md:col-span-3">Jenis Polarisasi IRU
                    <select name="polarisasi" required data-polarisasi-select class="mt-1 border rounded px-2 py-1.5 w-full">
                        <option value="minimize" @selected(old('polarisasi', 'minimize') === 'minimize')>Minimize — Batas Aman &amp; Batas Atas (nilai aktual diharapkan semakin rendah)</option>
                        <option value="maximize" @selected(old('polarisasi') === 'maximize')>Maximize — Batas Aman &amp; Batas Bawah (nilai aktual diharapkan semakin tinggi)</option>
                        <option value="stabilize" @selected(old('polarisasi') === 'stabilize')>Stabilize — Batas Aman (A &amp; B), Batas Atas &amp; Batas Bawah (nilai aktual pada rentang aman)</option>
                    </select>
                    <span data-polarisasi-hint class="mt-1 block text-xs text-slate-500"></span>
                </label>
                <label data-iru-field="minimize,maximize">BM · Batas Aman<input type="number" step="any" name="batas_aman" value="{{ old('batas_aman') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label data-iru-field="stabilize">BM atas · Batas Aman (A)<input type="number" step="any" name="batas_aman_atas" value="{{ old('batas_aman_atas') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label data-iru-field="stabilize">BM bawah · Batas Aman (B)<input type="number" step="any" name="batas_aman_bawah" value="{{ old('batas_aman_bawah') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label data-iru-field="minimize,stabilize">BA · Batas Atas<input type="number" step="any" name="batas_atas" value="{{ old('batas_atas') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label data-iru-field="maximize,stabilize">BB · Batas Bawah<input type="number" step="any" name="batas_bawah" value="{{ old('batas_bawah') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Periode pemantauan
                    <select name="periode_pemantauan" required class="mt-1 border rounded px-2 py-1.5 w-full">
                        <option value="bulanan" @selected(old('periode_pemantauan', 'bulanan') === 'bulanan')>Bulanan (diutamakan)</option>
                        <option value="triwulanan" @selected(old('periode_pemantauan') === 'triwulanan')>Triwulanan</option>
                        <option value="semesteran" @selected(old('periode_pemantauan') === 'semesteran')>Semesteran</option>
                    </select>
                </label>
                <label>Penanggung jawab<input name="penanggung_jawab" maxlength="150" value="{{ old('penanggung_jawab') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Penyedia data<input name="penyedia_data" maxlength="150" value="{{ old('penyedia_data') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Formula<textarea name="formula" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full">{{ old('formula') }}</textarea></label>
                <label class="md:col-span-3">Definisi<textarea name="definisi" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full">{{ old('definisi') }}</textarea></label>
                <label class="md:col-span-3">Sumber data<textarea name="sumber_data" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full">{{ old('sumber_data') }}</textarea></label>
                <button class="md:col-span-3 justify-self-start rounded bg-sky-700 px-4 py-2 text-white">Simpan indikator</button>
            </form>
        </details>
    </div>

    <div class="mt-4 space-y-3">
        @forelse($risiko->indikatorIru as $indikator)
            @php($pengukuranTerbaru = $indikator->pemantauanIru->first())
            <article class="rounded-md border border-slate-200 p-3">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">{{ $indikator->nama_indikator }}</h3>
                        <p class="text-xs text-slate-500">{{ ucfirst($indikator->polarisasi) }} · {{ $indikator->periode_pemantauan }} · Satuan: {{ $indikator->satuan }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('indikator-iru.manual-pdf', ['indikatorRisiko' => $indikator, 'tahun' => $tahunEvaluasi]) }}"
                           class="rounded bg-[#005FAC] px-2.5 py-1 text-xs font-semibold text-white hover:bg-[#004b8a]">
                            Cetak PDF Manual IRU
                        </a>
                        @if($pengukuranTerbaru)
                            @php($statusIru = $indikator->statusUntuk((float) $pengukuranTerbaru->nilai_aktual))
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-green-100 text-green-800' => $statusIru === 'Aman',
                                'bg-yellow-100 text-yellow-900' => $statusIru === 'Waspada',
                                'bg-red-100 text-red-800' => $statusIru === 'Awas',
                            ])>{{ $statusIru }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">Belum ada nilai aktual 6 bulan terakhir</span>
                        @endif
                    </div>
                </div>
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah indikator' : 'Ajukan perubahan indikator' }}</summary>
                    <form method="POST" action="{{ route('indikator-iru.update', $indikator) }}" data-iru-form class="mt-2 grid gap-2 rounded border p-3 text-sm md:grid-cols-3">
                        @csrf @method('PUT')
                        <label>Nama indikator<input name="nama_indikator" required maxlength="150" value="{{ $indikator->nama_indikator }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label>Satuan<input name="satuan" required maxlength="50" value="{{ $indikator->satuan }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label>Jenis konsolidasi periode
                            <select name="jenis_konsolidasi" class="mt-1 w-full rounded border px-2 py-1.5">
                                @foreach(\App\Models\IndikatorRisiko::JENIS_KONSOLIDASI as $kodeKonsolidasi => $labelKonsolidasi)
                                    <option value="{{ $kodeKonsolidasi }}" @selected(($indikator->jenis_konsolidasi ?? 'tidak_ada') === $kodeKonsolidasi)>{{ $labelKonsolidasi }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="md:col-span-3">Jenis Polarisasi IRU
                            <select name="polarisasi" required data-polarisasi-select class="mt-1 w-full rounded border px-2 py-1.5">
                                <option value="minimize" @selected($indikator->polarisasi === 'minimize')>Minimize — Batas Aman &amp; Batas Atas (semakin rendah semakin baik)</option>
                                <option value="maximize" @selected($indikator->polarisasi === 'maximize')>Maximize — Batas Aman &amp; Batas Bawah (semakin tinggi semakin baik)</option>
                                <option value="stabilize" @selected($indikator->polarisasi === 'stabilize')>Stabilize — Batas Aman (A &amp; B), Batas Atas &amp; Batas Bawah</option>
                            </select>
                            <span data-polarisasi-hint class="mt-1 block text-xs text-slate-500"></span>
                        </label>
                        <label data-iru-field="minimize,maximize">BM · Batas Aman<input type="number" step="any" name="batas_aman" value="{{ $indikator->batas_aman }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label data-iru-field="stabilize">BM atas · Batas Aman (A)<input type="number" step="any" name="batas_aman_atas" value="{{ $indikator->batas_aman_atas }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label data-iru-field="stabilize">BM bawah · Batas Aman (B)<input type="number" step="any" name="batas_aman_bawah" value="{{ $indikator->batas_aman_bawah }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label data-iru-field="minimize,stabilize">BA · Batas Atas<input type="number" step="any" name="batas_atas" value="{{ $indikator->batas_atas }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label data-iru-field="maximize,stabilize">BB · Batas Bawah<input type="number" step="any" name="batas_bawah" value="{{ $indikator->batas_bawah }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label>Periode pemantauan
                            <select name="periode_pemantauan" required class="mt-1 w-full rounded border px-2 py-1.5">
                                @foreach(['bulanan' => 'Bulanan', 'triwulanan' => 'Triwulanan', 'semesteran' => 'Semesteran'] as $value => $label)
                                    <option value="{{ $value }}" @selected($indikator->periode_pemantauan === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Penanggung jawab<input name="penanggung_jawab" maxlength="150" value="{{ $indikator->penanggung_jawab }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label>Penyedia data<input name="penyedia_data" maxlength="150" value="{{ $indikator->penyedia_data }}" class="mt-1 w-full rounded border px-2 py-1.5"></label>
                        <label>Formula<textarea name="formula" rows="2" class="mt-1 w-full rounded border px-2 py-1.5">{{ $indikator->formula }}</textarea></label>
                        <label class="md:col-span-3">Definisi<textarea name="definisi" rows="2" class="mt-1 w-full rounded border px-2 py-1.5">{{ $indikator->definisi }}</textarea></label>
                        <label class="md:col-span-3">Sumber data<textarea name="sumber_data" rows="2" class="mt-1 w-full rounded border px-2 py-1.5">{{ $indikator->sumber_data }}</textarea></label>
                        <button class="justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Simpan perubahan</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('indikator-iru.destroy', $indikator) }}" onsubmit="return confirm('Ajukan penghapusan indikator IRU?')" class="mt-2">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-700">{{ auth()->user()->isPrivileged() ? 'Hapus indikator' : 'Ajukan hapus indikator' }}</button>
                </form>
                <p class="mt-2 text-sm">{{ $indikator->definisi ?: 'Definisi belum diisi.' }}</p>
                <p class="mt-1 text-xs text-slate-600">
                    BM:
                    @if($indikator->polarisasi === 'stabilize')
                        {{ $indikator->batas_aman_bawah }}–{{ $indikator->batas_aman_atas }}
                    @else
                        {{ $indikator->batas_aman }}
                    @endif
                    · BA: {{ $indikator->batas_atas ?? '—' }} · BB: {{ $indikator->batas_bawah ?? '—' }}
                    · Penanggung jawab: {{ $indikator->penanggung_jawab ?: '—' }}
                    · Penyedia data: {{ $indikator->penyedia_data ?: '—' }}
                </p>
                @if($indikator->pemantauanIru->isNotEmpty())
                    <div class="mt-2 text-xs">
                        <span class="font-medium">Nilai aktual (6 bulan terakhir):</span>
                        @foreach($indikator->pemantauanIru as $pengukuran)
                            @php($statusPengukuran = $indikator->statusUntuk((float) $pengukuran->nilai_aktual))
                            <div class="mt-2 rounded border p-2">
                                <span>{{ $pengukuran->tanggal_pengukuran->format('d/m/Y') }}: {{ $pengukuran->nilai_aktual }} ({{ $statusPengukuran }})</span>
                                @if($pengukuran->jenis_pegawai_perbaikan || $pengukuran->jumlah_pegawai_perbaikan)
                                    <p class="mt-1 text-slate-600">
                                        Perbaikan IRU: {{ $pengukuran->jenis_pegawai_perbaikan ?: 'Jenis pegawai tidak diisi' }}
                                        · {{ $pengukuran->jumlah_pegawai_perbaikan ?? '—' }} pegawai
                                    </p>
                                @endif
                                @if($pengukuran->catatan_evaluasi)
                                    <p class="mt-1 text-slate-600">Evaluasi setelah perbaikan: {{ $pengukuran->catatan_evaluasi }}</p>
                                @endif
                                <details class="mt-1">
                                    <summary class="cursor-pointer text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah nilai' : 'Ajukan perubahan nilai' }}</summary>
                                    <form method="POST" action="{{ route('pemantauan-iru.update', $pengukuran) }}" class="mt-2 grid gap-2 md:grid-cols-4">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="triwulan" value="{{ $pengukuran->tanggal_pengukuran->quarter }}">
                                        <input type="hidden" name="tahun" value="{{ $pengukuran->tanggal_pengukuran->year }}">
                                                        <label>Tanggal<input type="date" name="tanggal_pengukuran" value="{{ $pengukuran->tanggal_pengukuran->toDateString() }}" onchange="const tanggal = new Date(this.value + 'T00:00:00'); this.form.elements.triwulan.value = Math.ceil((tanggal.getMonth() + 1) / 3); this.form.elements.tahun.value = tanggal.getFullYear();" required class="mt-1 w-full rounded border px-2 py-1"></label>
                                        <label>Nilai aktual<input type="number" step="any" name="nilai_aktual" value="{{ $pengukuran->nilai_aktual }}" required class="mt-1 w-full rounded border px-2 py-1"></label>
                                                        <label>Jenis pegawai perbaikan<input name="jenis_pegawai_perbaikan" maxlength="150" value="{{ $pengukuran->jenis_pegawai_perbaikan }}" placeholder="Contoh: Analis, operator" class="mt-1 w-full rounded border px-2 py-1"></label>
                                                        <label>Jumlah pegawai<input type="number" name="jumlah_pegawai_perbaikan" min="1" max="65535" value="{{ $pengukuran->jumlah_pegawai_perbaikan }}" class="mt-1 w-full rounded border px-2 py-1"></label>
                                                        <label class="md:col-span-2">Catatan<textarea name="catatan" rows="1" class="mt-1 w-full rounded border px-2 py-1">{{ $pengukuran->catatan }}</textarea></label>
                                                        <label class="md:col-span-4">Evaluasi setelah perbaikan<textarea name="catatan_evaluasi" rows="2" class="mt-1 w-full rounded border px-2 py-1">{{ $pengukuran->catatan_evaluasi }}</textarea></label>
                                        <button class="justify-self-start rounded bg-sky-700 px-3 py-1 text-white">Simpan</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('pemantauan-iru.destroy', $pengukuran) }}" onsubmit="return confirm('Ajukan penghapusan nilai aktual ini?')" class="mt-1">
                                    @csrf @method('DELETE')
                                    <button class="text-red-700">{{ auth()->user()->isPrivileged() ? 'Hapus nilai' : 'Ajukan hapus nilai' }}</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-sky-800">+ Catat nilai aktual</summary>
                    <form method="POST" action="{{ route('pemantauan-iru.store', $indikator) }}" class="grid md:grid-cols-4 gap-2 mt-2 text-sm">
                        @csrf
                        <input type="hidden" name="triwulan" value="{{ $triwulanEvaluasi }}">
                        <input type="hidden" name="tahun" value="{{ $tahunEvaluasi }}">
                        <label>Tanggal pengukuran<input type="date" name="tanggal_pengukuran" min="{{ $awalPeriode->toDateString() }}" max="{{ min($akhirPeriode->toDateString(), now()->toDateString()) }}" required class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                        <label>Nilai aktual<input type="number" step="any" name="nilai_aktual" required class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                        <label>Jenis pegawai perbaikan<input name="jenis_pegawai_perbaikan" maxlength="150" placeholder="Contoh: Analis, operator" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                        <label>Jumlah pegawai<input type="number" name="jumlah_pegawai_perbaikan" min="1" max="65535" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                        <label class="md:col-span-2">Catatan<textarea name="catatan" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                        <label class="md:col-span-4">Evaluasi setelah perbaikan<textarea name="catatan_evaluasi" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                        <button class="md:col-span-4 justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Simpan nilai</button>
                    </form>
                </details>
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada indikator IRU untuk risiko ini.</p>
        @endforelse
    </div>
</section>

<section class="bg-white rounded-lg shadow p-5 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <h2 class="text-lg font-semibold">Loss Event Database (LED)</h2>
            <p class="text-sm text-slate-500">Admin/SKKI dapat mencatat LED langsung. Pengiriman dari bidang lain wajib disertai bukti dan baru dihitung setelah diverifikasi.</p>
        </div>
        <details class="w-full mt-2">
            <summary class="cursor-pointer text-sm font-medium text-sky-800">+ Catat kejadian LED</summary>
            <form method="POST" action="{{ route('kejadian-led.store', $risiko) }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-3 mt-3 rounded-md border p-3 text-sm">
                @csrf
                <label>Waktu kejadian<input type="datetime-local" name="terjadi_pada" required max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <label>Waktu pelaporan (jika telah dilaporkan)<input type="datetime-local" name="dilaporkan_pada" max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 border rounded px-2 py-1.5 w-full"></label>
                <input type="hidden" name="kejadian_luar_biasa" value="0">
                <label class="flex items-center gap-2"><input type="checkbox" name="kejadian_luar_biasa" value="1"> Kejadian luar biasa</label>
                <label class="md:col-span-2">Uraian kejadian<textarea name="uraian_kejadian" rows="2" required class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                <label>Dampak<textarea name="dampak" rows="2" required class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                <label>Tindakan mitigasi<textarea name="tindakan_mitigasi" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                <label class="md:col-span-2">Kondisi setelah mitigasi<textarea name="kondisi_setelah_mitigasi" rows="2" class="mt-1 border rounded px-2 py-1.5 w-full"></textarea></label>
                @unless(auth()->user()->isPrivileged())
                    <label class="md:col-span-2">Bukti kejadian (PDF, gambar, atau dokumen; maks. 10 MB)
                        <input type="file" name="bukti_kejadian" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 w-full rounded border px-2 py-1.5">
                    </label>
                @endunless
                <button class="md:col-span-2 justify-self-start rounded bg-sky-700 px-4 py-2 text-white">Simpan kejadian</button>
            </form>
        </details>
    </div>
    @forelse($risiko->kejadianLed as $kejadian)
        <article class="mt-3 rounded-md border p-3 text-sm">
            <div class="flex flex-wrap justify-between gap-2">
                <div class="font-semibold">{{ $kejadian->terjadi_pada->format('d/m/Y H:i') }}</div>
                <span @class([
                    'rounded-full px-2 py-1 text-xs font-semibold',
                    'bg-amber-100 text-amber-900' => $kejadian->status_verifikasi === 'pending',
                    'bg-emerald-100 text-emerald-800' => $kejadian->status_verifikasi === 'approved',
                    'bg-red-100 text-red-800' => $kejadian->status_verifikasi === 'rejected',
                ])>{{ ['pending' => 'Menunggu validasi', 'approved' => 'Bukti terverifikasi', 'rejected' => 'Bukti ditolak'][$kejadian->status_verifikasi] }}</span>
                @if($kejadian->kejadian_luar_biasa)
                    <span @class([
                        'rounded-full px-2 py-1 text-xs font-semibold',
                        'bg-red-100 text-red-800' => $kejadian->terlambatDilaporkan(),
                    'bg-amber-100 text-amber-900' => ! $kejadian->dilaporkan_pada && ! $kejadian->terlambatDilaporkan(),
                    'bg-emerald-100 text-emerald-800' => $kejadian->dilaporkan_pada && ! $kejadian->terlambatDilaporkan(),
                    ])>
                        @if($kejadian->dilaporkan_pada)
                            {{ $kejadian->terlambatDilaporkan() ? 'Pelaporan terlambat' : 'Dilaporkan tepat waktu' }}
                        @else
                            {{ $kejadian->terlambatDilaporkan() ? 'Terlambat dilaporkan' : 'Belum dilaporkan' }}
                        @endif
                    </span>
                @endif
            </div>
            <p class="mt-1">{{ $kejadian->uraian_kejadian }}</p>
            <p class="mt-1 text-slate-600">Dampak: {{ $kejadian->dampak }}</p>
            @if($kejadian->tindakan_mitigasi)<p class="mt-1 text-slate-600">Mitigasi: {{ $kejadian->tindakan_mitigasi }}</p>@endif
            @if($kejadian->kondisi_setelah_mitigasi)<p class="mt-1 text-slate-600">Kondisi setelah mitigasi: {{ $kejadian->kondisi_setelah_mitigasi }}</p>@endif
            @if($kejadian->dilaporkan_pada)<p class="mt-1 text-xs text-slate-500">Dilaporkan: {{ $kejadian->dilaporkan_pada->format('d/m/Y H:i') }}</p>@endif
            @if($kejadian->bukti_path)
                <a class="mt-1 inline-block text-blue-700 underline" href="{{ route('kejadian-led.evidence', $kejadian) }}">Lihat bukti kejadian</a>
            @endif
            @if($kejadian->catatan_verifikasi)
                <p class="mt-1 text-xs text-slate-600">Catatan validasi: {{ $kejadian->catatan_verifikasi }}</p>
            @endif
            @if(auth()->user()->isPrivileged() && $kejadian->bukti_path)
                <form method="POST" action="{{ route('kejadian-led.verify', $kejadian) }}" class="mt-2 flex flex-wrap items-end gap-2 text-xs">
                    @csrf
                    <label>Catatan validasi<input name="catatan_verifikasi" maxlength="2000" value="{{ $kejadian->catatan_verifikasi }}" class="mt-1 rounded border px-2 py-1"></label>
                    <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan bukti</button>
                    <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak bukti</button>
                </form>
            @endif
            @if(auth()->user()->isPrivileged())
                <details class="mt-2">
                    <summary class="cursor-pointer text-sky-800">Ubah kejadian LED</summary>
                    <form method="POST" action="{{ route('kejadian-led.update', $kejadian) }}" enctype="multipart/form-data" class="mt-2 grid gap-2 md:grid-cols-2">
                        @csrf @method('PUT')
                        <label>Waktu kejadian<input type="datetime-local" name="terjadi_pada" required value="{{ $kejadian->terjadi_pada->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <label>Waktu pelaporan<input type="datetime-local" name="dilaporkan_pada" value="{{ $kejadian->dilaporkan_pada?->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <input type="hidden" name="kejadian_luar_biasa" value="0">
                        <label class="flex items-center gap-2"><input type="checkbox" name="kejadian_luar_biasa" value="1" @checked($kejadian->kejadian_luar_biasa)> Kejadian luar biasa</label>
                        <label class="md:col-span-2">Uraian kejadian<textarea name="uraian_kejadian" rows="2" required class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->uraian_kejadian }}</textarea></label>
                        <label>Dampak<textarea name="dampak" rows="2" required class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->dampak }}</textarea></label>
                        <label>Tindakan mitigasi<textarea name="tindakan_mitigasi" rows="2" class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->tindakan_mitigasi }}</textarea></label>
                        <label class="md:col-span-2">Kondisi setelah mitigasi<textarea name="kondisi_setelah_mitigasi" rows="2" class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->kondisi_setelah_mitigasi }}</textarea></label>
                        <label class="md:col-span-2">Ganti bukti (opsional)<input type="file" name="bukti_kejadian" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <button class="justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Simpan perubahan</button>
                    </form>
                </details>
            @else
                <details class="mt-2">
                    <summary class="cursor-pointer text-sky-800">Ajukan perubahan kejadian</summary>
                    <form method="POST" action="{{ route('kejadian-led.update', $kejadian) }}" enctype="multipart/form-data" class="mt-2 grid gap-2 md:grid-cols-2">
                        @csrf @method('PUT')
                        <label>Waktu kejadian<input type="datetime-local" name="terjadi_pada" required value="{{ $kejadian->terjadi_pada->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <label>Waktu pelaporan<input type="datetime-local" name="dilaporkan_pada" value="{{ $kejadian->dilaporkan_pada?->format('Y-m-d\TH:i') }}" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <input type="hidden" name="kejadian_luar_biasa" value="0">
                        <label class="flex items-center gap-2"><input type="checkbox" name="kejadian_luar_biasa" value="1" @checked($kejadian->kejadian_luar_biasa)> Kejadian luar biasa</label>
                        <label class="md:col-span-2">Uraian kejadian<textarea name="uraian_kejadian" rows="2" required class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->uraian_kejadian }}</textarea></label>
                        <label>Dampak<textarea name="dampak" rows="2" required class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->dampak }}</textarea></label>
                        <label>Tindakan mitigasi<textarea name="tindakan_mitigasi" rows="2" class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->tindakan_mitigasi }}</textarea></label>
                        <label class="md:col-span-2">Kondisi setelah mitigasi<textarea name="kondisi_setelah_mitigasi" rows="2" class="mt-1 w-full rounded border px-2 py-1">{{ $kejadian->kondisi_setelah_mitigasi }}</textarea></label>
                        <label class="md:col-span-2">Bukti pengganti wajib diunggah<input type="file" name="bukti_kejadian" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-1 w-full rounded border px-2 py-1"></label>
                        <button class="justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Ajukan perubahan</button>
                    </form>
                </details>
            @endif
            <form method="POST" action="{{ route('kejadian-led.destroy', $kejadian) }}" onsubmit="return confirm('Ajukan penghapusan kejadian ini?')" class="mt-2">
                @csrf @method('DELETE')
                <button class="text-xs text-red-700">{{ auth()->user()->isPrivileged() ? 'Hapus kejadian' : 'Ajukan hapus kejadian' }}</button>
            </form>
            @if(auth()->user()->isPrivileged() && $kejadian->kejadian_luar_biasa)
                <form method="POST" action="{{ route('kejadian-led.report', $kejadian) }}" class="mt-2 flex flex-wrap items-end gap-2 text-xs">
                    @csrf @method('PUT')
                    <label>Waktu pelaporan
                        <input type="datetime-local" name="dilaporkan_pada" required
                            value="{{ old('dilaporkan_pada', $kejadian->dilaporkan_pada?->format('Y-m-d\TH:i')) }}"
                            max="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 border rounded px-2 py-1">
                    </label>
                    <button class="border rounded px-3 py-1.5">Perbarui pelaporan</button>
                </form>
                <p class="mt-1 text-xs text-slate-500">Batas pelaporan: {{ $kejadian->terjadi_pada->copy()->addDay()->format('d/m/Y H:i') }}</p>
            @endif
        </article>
    @empty
        <p class="mt-3 text-sm text-slate-500">Tidak ada kejadian LED pada triwulan ini.</p>
    @endforelse
</section>

<section class="bg-white rounded-lg shadow p-5 mb-6">
    <h2 class="text-lg font-semibold mb-1">Rekomendasi Risiko Triwulanan</h2>
    <p class="text-sm text-slate-500 mb-4">
        Rekomendasi baru dibuat langsung oleh Admin atau SKKI. Perubahan atau penghapusan dari bidang lain memerlukan persetujuan.
    </p>

    @if(auth()->user()->isPrivileged())
    <form method="POST" action="{{ route('rekomendasi.store', $risiko) }}" class="grid md:grid-cols-6 gap-3 text-sm">
        @csrf
        <div class="text-sm">
            <span class="block text-xs mb-1">Triwulan evaluasi</span>
            <span class="font-medium">Triwulan {{ ['I', 'II', 'III', 'IV'][$triwulanEvaluasi - 1] }}</span>
            <input type="hidden" name="triwulan" value="{{ $triwulanEvaluasi }}">
        </div>
        <div class="text-sm">
            <span class="block text-xs mb-1">Tahun evaluasi</span>
            <span class="font-medium">{{ $tahunEvaluasi }}</span>
            <input type="hidden" name="tahun" value="{{ $tahunEvaluasi }}">
        </div>
        <label class="block">
            <span class="block text-xs mb-1">Level kemungkinan (opsional)</span>
            <select name="rekomendasi_level_kemungkinan" class="border rounded px-2 py-1 w-full">
                <option value="">Pilih level</option>
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('rekomendasi_level_kemungkinan') == $i)>{{ $i }}</option>
                @endfor
            </select>
        </label>
        <label class="block">
            <span class="block text-xs mb-1">Level dampak (opsional)</span>
            <select name="rekomendasi_level_dampak" class="border rounded px-2 py-1 w-full">
                <option value="">Pilih level</option>
                @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" @selected(old('rekomendasi_level_dampak') == $i)>{{ $i }}</option>
                @endfor
            </select>
        </label>
        <label class="block md:col-span-6">
            <span class="block text-xs mb-1">Uraian rekomendasi</span>
            <textarea name="deskripsi_rekomendasi" rows="3" required class="border rounded px-2 py-1 w-full">{{ old('deskripsi_rekomendasi') }}</textarea>
        </label>
        <div class="md:col-span-6">
            <button class="bg-slate-900 text-white rounded px-4 py-2">Simpan rekomendasi</button>
        </div>
    </form>
    @else
        <p class="mb-4 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-slate-700">
            Pengisian rekomendasi triwulanan dikhususkan untuk Admin dan SKKI.
        </p>
    @endif

    @if($risiko->rekomendasiRisiko->isNotEmpty())
        <h3 class="font-semibold mt-6 mb-2">Rekomendasi tersimpan</h3>
        <div class="space-y-3">
            @foreach($risiko->rekomendasiRisiko as $rekomendasi)
                @if(auth()->user()->isPrivileged())
                <form method="POST" action="{{ route('rekomendasi.update', $rekomendasi) }}" class="border rounded p-3 grid md:grid-cols-6 gap-3 text-sm">
                    @csrf @method('PUT')
                    <label class="block">
                        <span class="block text-xs mb-1">Triwulan</span>
                        <select name="triwulan" class="border rounded px-2 py-1 w-full" required>
                            @foreach([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $nomor => $nama)
                                <option value="{{ $nomor }}" @selected((int) $rekomendasi->triwulan === $nomor)>Triwulan {{ $nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="block text-xs mb-1">Tahun</span>
                        <input type="number" name="tahun" min="2020" max="2100" value="{{ $rekomendasi->tahun }}" required class="border rounded px-2 py-1 w-full">
                    </label>
                    <label class="block">
                        <span class="block text-xs mb-1">Level kemungkinan</span>
                        <select name="rekomendasi_level_kemungkinan" class="border rounded px-2 py-1 w-full">
                            <option value="">Tidak diisi</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_kemungkinan === $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block">
                        <span class="block text-xs mb-1">Level dampak</span>
                        <select name="rekomendasi_level_dampak" class="border rounded px-2 py-1 w-full">
                            <option value="">Tidak diisi</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_dampak === $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block md:col-span-6">
                        <span class="block text-xs mb-1">Uraian rekomendasi</span>
                        <textarea name="deskripsi_rekomendasi" rows="3" required class="border rounded px-2 py-1 w-full">{{ $rekomendasi->deskripsi_rekomendasi }}</textarea>
                    </label>
                    <div class="md:col-span-6 flex items-center justify-between">
                        <span class="text-slate-500">
                            @if($rekomendasi->rekomendasi_besaran)
                                Besaran: {{ $rekomendasi->rekomendasi_level_kemungkinan }} × {{ $rekomendasi->rekomendasi_level_dampak }}
                                @include('partials.badge', ['besaran' => $rekomendasi->rekomendasi_besaran])
                            @else
                                Besaran risiko belum diisi.
                            @endif
                        </span>
                        <button class="border rounded px-3 py-1.5">Perbarui rekomendasi</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('rekomendasi.destroy', $rekomendasi) }}" onsubmit="return confirm('Hapus rekomendasi ini?')" class="mt-2">
                    @csrf @method('DELETE')
                    <button class="rounded border border-red-300 px-3 py-1.5 text-red-700">Hapus rekomendasi</button>
                </form>
                @else
                    <article class="rounded-md border border-slate-200 p-3 text-sm">
                        <div class="mb-2 font-semibold text-slate-700">Triwulan {{ $rekomendasi->triwulan }}/{{ $rekomendasi->tahun }}</div>
                        <p class="text-slate-700">{{ $rekomendasi->deskripsi_rekomendasi }}</p>
                        @if($rekomendasi->rekomendasi_besaran)
                            <div class="mt-2">
                                Besaran: {{ $rekomendasi->rekomendasi_level_kemungkinan }} × {{ $rekomendasi->rekomendasi_level_dampak }}
                                @include('partials.badge', ['besaran' => $rekomendasi->rekomendasi_besaran])
                            </div>
                        @endif
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sky-800">Ajukan perubahan rekomendasi</summary>
                            <form method="POST" action="{{ route('rekomendasi.update', $rekomendasi) }}" class="mt-2 grid gap-2 md:grid-cols-2">
                                @csrf @method('PUT')
                                <label>Triwulan
                                    <select name="triwulan" required class="mt-1 w-full rounded border px-2 py-1">
                                        @foreach([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $nomor => $nama)
                                            <option value="{{ $nomor }}" @selected((int) $rekomendasi->triwulan === $nomor)>Triwulan {{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>Tahun<input type="number" name="tahun" min="2020" max="2100" value="{{ $rekomendasi->tahun }}" required class="mt-1 w-full rounded border px-2 py-1"></label>
                                <label>Level kemungkinan
                                    <select name="rekomendasi_level_kemungkinan" class="mt-1 w-full rounded border px-2 py-1">
                                        <option value="">Tidak diisi</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_kemungkinan === $i)>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </label>
                                <label>Level dampak
                                    <select name="rekomendasi_level_dampak" class="mt-1 w-full rounded border px-2 py-1">
                                        <option value="">Tidak diisi</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @selected((int) $rekomendasi->rekomendasi_level_dampak === $i)>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </label>
                                <label class="md:col-span-2">Uraian rekomendasi<textarea name="deskripsi_rekomendasi" rows="3" required class="mt-1 w-full rounded border px-2 py-1">{{ $rekomendasi->deskripsi_rekomendasi }}</textarea></label>
                                <button class="justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Ajukan perubahan</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('rekomendasi.destroy', $rekomendasi) }}" onsubmit="return confirm('Ajukan penghapusan rekomendasi ini?')" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="text-red-700">Ajukan hapus rekomendasi</button>
                        </form>
                    </article>
                @endif
            @endforeach
        </div>
    @endif
</section>

<h2 class="text-lg font-semibold mb-2">Rencana & Progres Mitigasi</h2>

@forelse($risiko->rencana as $rencana)
<div class="bg-white rounded-lg shadow p-5 mb-4">
    <div class="mb-3 flex flex-wrap items-center gap-2 text-sm">
        <span class="font-semibold">Rencana #{{ $rencana->id }}</span>
        <span @class([
            'rounded-full px-2 py-1 text-xs font-semibold',
            'bg-amber-100 text-amber-900' => $rencana->status_verifikasi === 'pending',
            'bg-emerald-100 text-emerald-800' => $rencana->status_verifikasi === 'approved',
            'bg-red-100 text-red-800' => $rencana->status_verifikasi === 'rejected',
        ])>{{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$rencana->status_verifikasi] ?? 'Belum divalidasi' }}</span>
        @if($rencana->bukti_path)
            <a class="text-blue-700 underline" href="{{ route('rencana.evidence', $rencana) }}">Lihat bukti rencana</a>
        @endif
    </div>
    @if($rencana->catatan_verifikasi)
        <p class="mb-3 text-xs text-slate-600">Catatan validasi: {{ $rencana->catatan_verifikasi }}</p>
    @endif
    <div class="flex items-start justify-between gap-3">
        <form method="POST" action="{{ route('rencana.update', $rencana) }}" enctype="multipart/form-data" class="flex-1 grid gap-2 md:grid-cols-2">
            @csrf @method('PUT')
            <textarea name="deskripsi_rencana" rows="2" required class="border rounded px-2 py-1 w-full text-sm md:col-span-2">{{ $rencana->deskripsi_rencana }}</textarea>
            <input type="file" name="bukti_mitigasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" @required(! auth()->user()->isPrivileged()) class="rounded border px-2 py-1 text-sm">
            <button class="justify-self-start rounded border px-3 text-sm">{{ auth()->user()->isPrivileged() ? 'Simpan' : 'Ajukan ubah' }}</button>
        </form>
        <form method="POST" action="{{ route('rencana.destroy', $rencana) }}" onsubmit="return confirm('Hapus rencana ini?')">
            @csrf @method('DELETE')
            <button class="text-red-700 text-sm">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan hapus' }}</button>
        </form>
    </div>
    @if(auth()->user()->isPrivileged() && $rencana->status_verifikasi === 'pending')
        <form method="POST" action="{{ route('rencana.verify', $rencana) }}" class="mt-3 flex flex-wrap items-end gap-2 text-sm">
            @csrf
            <label class="min-w-64 flex-1">Catatan validasi<input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border px-2 py-1"></label>
            <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan rencana</button>
            <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak rencana</button>
        </form>
    @endif

    <table class="w-full text-sm mt-4">
        <thead class="bg-slate-50 text-left">
            <tr><th class="p-2">Periode</th><th class="p-2">Progres</th><th class="p-2">Bukti</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($rencana->progres as $p)
            <tr class="border-t align-top">
                <td class="p-2 whitespace-nowrap">{{ $bulan[$p->bulan] }} {{ $p->tahun }}</td>
                <td class="p-2">{{ $p->deskripsi_progres }}</td>
                <td class="p-2">
                    @if($p->bukti_path || $p->file_bukti)<a class="text-blue-700 hover:underline" href="{{ route('progres.evidence', $p) }}">Lihat</a>@else — @endif
                    <div class="mt-1">
                        <span @class([
                            'rounded-full px-2 py-1 text-xs font-semibold',
                            'bg-amber-100 text-amber-900' => $p->status_verifikasi === 'pending',
                            'bg-emerald-100 text-emerald-800' => $p->status_verifikasi === 'approved',
                            'bg-red-100 text-red-800' => $p->status_verifikasi === 'rejected',
                        ])>{{ ['pending' => 'Menunggu validasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'][$p->status_verifikasi] ?? 'Belum divalidasi' }}</span>
                    </div>
                    @if($p->catatan_verifikasi)
                        <p class="mt-1 text-xs text-slate-600">{{ $p->catatan_verifikasi }}</p>
                    @endif
                </td>
                <td class="p-2">
                    <details>
                        <summary class="cursor-pointer text-sky-800">{{ auth()->user()->isPrivileged() ? 'Ubah' : 'Ajukan ubah' }}</summary>
                        <form method="POST" action="{{ route('progres.update', $p) }}" enctype="multipart/form-data" class="grid gap-2 rounded border p-2 md:grid-cols-2">
                            @csrf @method('PUT')
                            <input type="number" name="bulan" min="1" max="12" value="{{ $p->bulan }}" required class="rounded border px-2 py-1">
                            <input type="number" name="tahun" min="2020" max="2100" value="{{ $p->tahun }}" required class="rounded border px-2 py-1">
                            <textarea name="deskripsi_progres" rows="2" required class="md:col-span-2 rounded border px-2 py-1">{{ $p->deskripsi_progres }}</textarea>
                            <input type="file" name="bukti_mitigasi" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" @required(! auth()->user()->isPrivileged()) class="md:col-span-2 rounded border px-2 py-1">
                            <button class="justify-self-start rounded bg-sky-700 px-3 py-1.5 text-white">Simpan</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('progres.destroy', $p) }}" onsubmit="return confirm('Ajukan penghapusan progres?')" class="mt-1">
                        @csrf @method('DELETE') <button class="text-red-700">{{ auth()->user()->isPrivileged() ? 'Hapus' : 'Ajukan hapus' }}</button>
                    </form>
                </td>
            </tr>
            @if(auth()->user()->isPrivileged() && $p->status_verifikasi === 'pending')
                <tr class="border-t">
                    <td colspan="4" class="p-2">
                        <form method="POST" action="{{ route('progres.verify', $p) }}" class="flex flex-wrap items-end gap-2 text-sm">
                            @csrf
                            <label class="min-w-64 flex-1">Catatan validasi<input name="catatan_verifikasi" maxlength="2000" class="mt-1 w-full rounded border px-2 py-1"></label>
                            <button name="status_verifikasi" value="approved" class="rounded bg-emerald-700 px-3 py-1.5 text-white">Sahkan progres</button>
                            <button name="status_verifikasi" value="rejected" class="rounded bg-red-700 px-3 py-1.5 text-white">Tolak progres</button>
                        </form>
                    </td>
                </tr>
            @endif
        @empty
            <tr><td colspan="4" class="p-3 text-slate-400 text-center">Belum ada progres.</td></tr>
        @endforelse
        </tbody>
    </table>

    <details class="mt-4">
        <summary class="cursor-pointer text-sm text-blue-700">+ Tambah progres</summary>
        <form method="POST" action="{{ route('progres.store', $rencana) }}" enctype="multipart/form-data" class="grid md:grid-cols-4 gap-3 mt-3 text-sm">
            @csrf
            <select name="bulan" class="border rounded px-2 py-1" required>
                @foreach($bulan as $n => $nama)<option value="{{ $n }}" @selected(now()->month == $n)>{{ $nama }}</option>@endforeach
            </select>
            <input type="number" name="tahun" value="{{ now()->year }}" class="border rounded px-2 py-1" required>
            <input type="file" name="bukti_mitigasi" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="md:col-span-2 border rounded px-2 py-1">
            <textarea name="deskripsi_progres" rows="2" placeholder="Deskripsi progres" required class="md:col-span-3 border rounded px-2 py-1"></textarea>
            <button class="bg-slate-900 text-white rounded px-3">Simpan</button>
        </form>
    </details>
</div>
@empty
    <p class="text-sm text-slate-500 mb-4">Belum ada rencana mitigasi.</p>
@endforelse

<form method="POST" action="{{ route('rencana.store', $risiko) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-4 grid gap-2 md:grid-cols-2">
    @csrf
    <textarea name="deskripsi_rencana" rows="2" required placeholder="Tambah rencana mitigasi baru…" class="border rounded px-2 py-1 w-full text-sm md:col-span-2"></textarea>
    <input type="file" name="bukti_mitigasi" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="rounded border px-2 py-1 text-sm">
    <button class="justify-self-start bg-slate-900 text-white rounded px-4 text-sm">Tambah rencana</button>
</form>

<script>
    (function () {
        const hints = {
            minimize: 'Polarisasi Minimize: hanya menggunakan Batas Aman (BM) dan Batas Atas (BA). Nilai aktual diharapkan semakin rendah.',
            maximize: 'Polarisasi Maximize: hanya menggunakan Batas Aman (BM) dan Batas Bawah (BB). Nilai aktual diharapkan semakin tinggi.',
            stabilize: 'Polarisasi Stabilize: menggunakan Batas Aman (A & B), Batas Atas (BA), dan Batas Bawah (BB). Nilai aktual diharapkan berada dalam rentang aman.'
        };

        function syncIruForm(form) {
            const select = form.querySelector('[data-polarisasi-select]');
            if (!select) return;
            const mode = select.value;
            const hintEl = form.querySelector('[data-polarisasi-hint]');
            if (hintEl) {
                hintEl.textContent = hints[mode] || '';
            }

            form.querySelectorAll('[data-iru-field]').forEach((wrapper) => {
                const allowed = wrapper.getAttribute('data-iru-field').split(',');
                const visible = allowed.includes(mode);
                wrapper.classList.toggle('hidden', !visible);
                wrapper.querySelectorAll('input, select, textarea').forEach((input) => {
                    input.disabled = !visible;
                    input.required = visible;
                });
            });
        }

        document.querySelectorAll('[data-iru-form]').forEach((form) => {
            const select = form.querySelector('[data-polarisasi-select]');
            if (select) {
                select.addEventListener('change', () => syncIruForm(form));
            }
            syncIruForm(form);
        });
    })();
</script>
@endsection
