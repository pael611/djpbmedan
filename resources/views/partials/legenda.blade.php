<div class="flex flex-wrap gap-3 text-xs">
    @foreach([[1,'1–5 Sangat Rendah'],[6,'6–11 Rendah'],[12,'12–15 Sedang'],[16,'16–19 Tinggi'],[20,'20–25 Sangat Tinggi']] as [$n,$t])
        @php $c = \App\Models\Risiko::klasifikasi($n); @endphp
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded" style="background: {{ $c['hex'] }}"></span>{{ $t }}</span>
    @endforeach
</div>
