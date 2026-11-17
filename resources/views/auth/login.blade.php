@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="flex min-h-[calc(100vh-8rem)] items-center justify-center py-6">
    <div class="w-full max-w-sm overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        <div class="h-1.5 bg-[#00A2E9]">
            <div class="h-full w-1/3 bg-[#FAB715]"></div>
        </div>

        <div class="p-6 sm:p-7">
            <div class="mb-6 flex items-center gap-3">
                <img src="{{ asset('image/Logo.png') }}" alt="Logo Kementerian Keuangan Republik Indonesia" class="h-16 w-16 shrink-0 object-contain">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#005FAC]">Kementerian Keuangan</p>
                    <h1 class="mt-1 text-lg font-bold leading-tight text-slate-900">Sistem</h1>
                </div>
            </div>

            <p class="mb-5 text-sm text-slate-500">Masuk untuk melanjutkan kuliah ke akun Anda.</p>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#00A2E9] focus:ring-2 focus:ring-[#00A2E9]/20">
                    @error('username')
                        <p role="alert" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#00A2E9] focus:ring-2 focus:ring-[#00A2E9]/20">
                    @error('password')
                        <p role="alert" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-[#005FAC]">
                    Ingat saya
                </label>
                <button type="submit" class="w-full rounded-lg bg-[#005FAC] py-2.5 text-sm font-semibold text-white transition hover:bg-[#004b8a] focus:outline-none focus:ring-2 focus:ring-[#00A2E9] focus:ring-offset-2">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
