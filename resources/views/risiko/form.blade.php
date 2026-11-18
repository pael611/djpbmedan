@extends('layouts.app')
@section('title', $risiko->exists ? 'Ubah Risiko' : 'Tambah Risiko')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $risiko->exists ? 'Ubah' : 'Tambah' }} Kejadian Risiko</h1>

<form method="POST" action="{{ $risiko->exists ? route('risiko.update', $risiko) : route('risiko.store') }}"
      class="bg-white rounded-lg shadow p-6 space-y-4 max-w-3xl">
    @csrf
    @if($risiko->exists) @method('PUT') @endif

    @if(auth()->user()->isPrivileged())
    <div>
        <label class="block text-sm mb-1">Bidang</label>
        <select name="bidang_id" class="border rounded px-3 py-2 w-full" required>
            @foreach($bidangList as $b)
                <option value="{{ $b->id }}" @selected(old('bidang_id', $risiko->bidang_id) == $b->id)>{{ $b->nama }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div>
        <label class="block text-sm mb-1">Sasaran</label>
        <textarea name="sasaran" rows="2" required class="border rounded px-3 py-2 w-full">{{ old('sasaran', $risiko->sasaran) }}</textarea>
    </div>
    <div>
        <label class="block text-sm mb-1">Kejadian Risiko</label>
        <textarea name="kejadian_risiko" rows="3" required class="border rounded px-3 py-2 w-full">{{ old('kejadian_risiko', $risiko->kejadian_risiko) }}</textarea>
    </div>
    <div>
        <label for="jenis_risiko" class="block text-sm mb-1">Jenis Risiko</label>
        <select id="jenis_risiko" name="jenis_risiko" class="border rounded px-3 py-2 w-full" required>
            @foreach(\App\Models\Risiko::JENIS_RISIKO as $jenis => $label)
                <option value="{{ $jenis }}" @selected(old('jenis_risiko', $risiko->jenis_risiko) === $jenis)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-sm text-slate-600">
            <span class="font-semibold">Informasi:</span>
            Dasar utama penentuan risiko adalah Sasaran Organisasi (SO). Peristiwa tanpa dampak terhadap SO bukan risiko.
            Downside Risk berdampak negatif (menggagalkan, menunda, menghambat, atau mengurangi optimalisasi pencapaian SO);
            semakin tinggi levelnya, semakin besar hambatannya. Upside Risk adalah peluang berdampak positif yang meningkatkan
            keberhasilan pencapaian SO; semakin tinggi levelnya, semakin besar dampak positifnya.
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
        <div>
            <label class="block text-sm mb-1">Level Kemungkinan (1-5)</label>
            <select id="k" name="level_kemungkinan" class="border rounded px-3 py-2 w-full" required>
                @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('level_kemungkinan', $risiko->level_kemungkinan ?? 1) == $i)>{{ $i }}</option>@endfor
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Level Dampak (1-5)</label>
            <select id="d" name="level_dampak" class="border rounded px-3 py-2 w-full" required>
                @for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('level_dampak', $risiko->level_dampak ?? 1) == $i)>{{ $i }}</option>@endfor
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Triwulan</label>
            <select name="triwulan" class="border rounded px-3 py-2 w-full" required>
                @for($i=1;$i<=4;$i++)<option value="{{ $i }}" @selected(old('triwulan', $risiko->triwulan) == $i)>TW {{ $i }}</option>@endfor
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Tahun</label>
            <input type="number" name="tahun" value="{{ old('tahun', $risiko->tahun) }}" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block text-sm mb-1">Kategori Risiko (opsional)</label>
            <select name="kategori_risiko" class="border rounded px-3 py-2 w-full">
                <option value="">Pilih kategori</option>
                @foreach(\App\Models\Risiko::KATEGORI_RISIKO as $kategori => $urutan)
                    <option value="{{ $kategori }}" @selected(old('kategori_risiko', $risiko->kategori_risiko) === $kategori)>{{ $kategori }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Bobot Area Dampak (1–7, opsional)</label>
            <input type="number" name="bobot_area_dampak" min="1" max="7" value="{{ old('bobot_area_dampak', $risiko->bobot_area_dampak) }}" class="border rounded px-3 py-2 w-full">
        </div>
    </div>

    <div class="text-sm">Besaran risiko: <span id="preview" class="inline-block px-2 py-0.5 rounded font-semibold"></span></div>

    <div class="flex gap-2">
        <button class="bg-slate-900 text-white rounded px-5 py-2 hover:bg-slate-700">Simpan</button>
        <a href="{{ url()->previous() }}" class="px-5 py-2 border rounded">Batal</a>
    </div>
</form>

<script>
    // Pratinjau besaran & warna (perhitungan resmi tetap dilakukan di server)
    const matriksBesaranRisiko = [
        [1, 5, 10, 15, 20],
        [2, 6, 11, 16, 21],
        [3, 8, 13, 18, 23],
        [4, 9, 14, 19, 24],
        [7, 12, 17, 22, 25],
    ];
    function hitungBesaranRisiko(kemungkinan, dampak) {
        return matriksBesaranRisiko[kemungkinan - 1][dampak - 1];
    }
    const warna = b => b >= 20 ? ['#B71C1C','#fff','Sangat Tinggi'] : b >= 16 ? ['#E65100','#fff','Tinggi']
        : b >= 12 ? ['#F9A825','#000','Sedang'] : b >= 6 ? ['#1B5E20','#fff','Rendah'] : ['#0D47A1','#fff','Sangat Rendah'];
    function hitung() {
        const kemungkinan = Number(document.getElementById('k').value);
        const dampak = Number(document.getElementById('d').value);
        const b = hitungBesaranRisiko(kemungkinan, dampak);
        const [bg, fg, label] = warna(b);
        const el = document.getElementById('preview');
        el.textContent = b + ' · ' + label; el.style.background = bg; el.style.color = fg;
    }
    document.getElementById('k').addEventListener('change', hitung);
    document.getElementById('d').addEventListener('change', hitung);
    hitung();
</script>
@endsection
