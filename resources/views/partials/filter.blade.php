<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    @if(request()->filled('kejadian_risiko'))
        <input type="hidden" name="kejadian_risiko" value="{{ request('kejadian_risiko') }}">
    @endif
    <div>
        <label class="block text-xs mb-1">Tahun</label>
        <input type="number" name="tahun" value="{{ $tahun ?? now()->year }}" onchange="this.form.requestSubmit()" class="w-24 rounded-md border border-slate-300 px-2 py-1.5 focus:border-[#00A2E9] focus:outline-none focus:ring-2 focus:ring-[#00A2E9]/20">
    </div>
    @isset($daftarBulan)
    <div>
        <label class="block text-xs mb-1">Bulan</label>
        <select name="bulan" onchange="this.form.requestSubmit()" class="rounded-md border border-slate-300 px-2 py-1.5 focus:border-[#00A2E9] focus:outline-none focus:ring-2 focus:ring-[#00A2E9]/20">
            <option value="">Semua Bulan</option>
            @foreach($daftarBulan as $nomorBulan => $namaBulan)
                <option value="{{ $nomorBulan }}" @selected((int) request('bulan') === $nomorBulan)>{{ $namaBulan }}</option>
            @endforeach
        </select>
    </div>
    @endisset
    <div>
        <label class="block text-xs mb-1">Triwulan</label>
        <select name="triwulan" onchange="this.form.requestSubmit()" class="rounded-md border border-slate-300 px-2 py-1.5 focus:border-[#00A2E9] focus:outline-none focus:ring-2 focus:ring-[#00A2E9]/20">
            <option value="">Semua</option>
            @for($i=1;$i<=4;$i++)<option value="{{ $i }}" @selected(request('triwulan')==$i)>TW {{ $i }}</option>@endfor
        </select>
    </div>
    @isset($bidangList)
        @if(auth()->user()->isPrivileged())
        <div>
            <label class="block text-xs mb-1">Bidang</label>
            <select name="bidang_id" onchange="this.form.requestSubmit()" class="rounded-md border border-slate-300 px-2 py-1.5 focus:border-[#00A2E9] focus:outline-none focus:ring-2 focus:ring-[#00A2E9]/20">
                <option value="">Semua</option>
                @foreach($bidangList as $b)<option value="{{ $b->id }}" @selected(request('bidang_id')==$b->id)>{{ $b->nama }}</option>@endforeach
            </select>
        </div>
        @endif
    @endisset
    <button class="rounded-md bg-[#005FAC] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#004b8a] focus:outline-none focus:ring-2 focus:ring-[#00A2E9] focus:ring-offset-2">Terapkan</button>
</form>
