@extends('layouts.app')
@section('title', 'Masuk — PARIBAN')
@section('content')
    <section class="flex min-h-[calc(100vh-8rem)] items-center justify-center px-2 py-8">
        <div
            class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-900/5">
            {{-- Aksen garis atas Kemenkeu / DJPb --}}
            <div class="flex h-2 w-full">
                <div class="w-1/3 bg-[#FAB715]"></div>
                <div class="w-2/3 bg-[#005FAC]"></div>
            </div>

            <div class="p-7 sm:p-8">
                {{-- Identitas Aplikasi --}}
                <div class="mb-6 flex items-center gap-3.5 border-b border-slate-100 pb-5">
                    <img src="{{ asset('image/Logo.png') }}" alt="Logo Kementerian Keuangan Republik Indonesia"
                        class="h-14 w-14 shrink-0 object-contain">
                    <div>
                        <p class="text-xs font-semibold text-[#005FAC]">Kanwil DJPb Provinsi Sumatera Utara</p>
                        <h1 class="mt-0.5 text-xl font-bold leading-tight text-slate-900">PARIBAN</h1>
                        <p class="text-xs text-slate-500">Pemantauan Risiko Berbasis Online</p>
                    </div>
                </div>

                <p class="mb-5 text-sm leading-relaxed text-slate-600">
                    Silakan masukkan <strong>Username</strong> dan <strong>Password</strong> akun bidang Anda untuk masuk.
                </p>

                @if ($errors->any())
                    <div role="alert"
                        class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Input Username --}}
                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-semibold text-slate-800">
                            Username
                        </label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}"
                            placeholder="Contoh: admin, skki, umum" required autofocus autocomplete="username"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:border-[#005FAC] focus:outline-none focus:ring-2 focus:ring-[#005FAC]/20">
                        @error('username')
                            <p role="alert" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Input Password + Tombol Tampilkan Password --}}
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-800">
                            Password
                        </label>
                        <div class="relative flex items-center">
                            <input id="password" type="password" name="password" placeholder="Masukkan password Anda"
                                required autocomplete="current-password"
                                class="w-full rounded-lg border border-slate-300 bg-white py-3 pl-3.5 pr-32 text-base text-slate-900 placeholder:text-slate-400 focus:border-[#005FAC] focus:outline-none focus:ring-2 focus:ring-[#005FAC]/20">
                            <button type="button" id="toggle-password" aria-controls="password" aria-pressed="false"
                                class="absolute right-2 inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#005FAC]/30">
                                <svg id="icon-eye-open" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg id="icon-eye-closed" class="hidden h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                                <span id="toggle-password-text">Tampilkan</span>
                            </button>
                        </div>
                        <p id="capslock-warning" class="mt-1.5 hidden text-xs font-medium text-amber-700">
                            Perhatian: tombol Caps Lock sedang aktif (huruf besar).
                        </p>
                        @error('password')
                            <p role="alert" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ingat Saya --}}
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-slate-700">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-[#005FAC]">
                        <span>Ingat saya di perangkat ini</span>
                    </label>

                    {{-- Tombol Masuk --}}
                    <button type="submit"
                        class="w-full rounded-lg bg-[#005FAC] py-3 text-base font-semibold text-white transition hover:bg-[#004b8a] focus:outline-none focus:ring-2 focus:ring-[#005FAC] focus:ring-offset-2">
                        Masuk ke Aplikasi
                    </button>
                </form>

                <p class="mt-5 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">
                    Mengalami kendala masuk atau lupa password? Hubungi Admin Kanwil DJPb Sumatera Utara.
                </p>
            </div>
        </div>
    </section>

    <script>
        (function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password');
            const toggleText = document.getElementById('toggle-password-text');
            const iconOpen = document.getElementById('icon-eye-open');
            const iconClosed = document.getElementById('icon-eye-closed');
            const capsWarning = document.getElementById('capslock-warning');

            if (passwordInput && toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const showing = passwordInput.type === 'text';
                    passwordInput.type = showing ? 'password' : 'text';
                    toggleBtn.setAttribute('aria-pressed', showing ? 'false' : 'true');
                    if (toggleText) toggleText.textContent = showing ? 'Tampilkan' : 'Sembunyikan';
                    iconOpen?.classList.toggle('hidden', !showing);
                    iconClosed?.classList.toggle('hidden', showing);
                    passwordInput.focus();
                });
            }

            if (passwordInput && capsWarning) {
                const checkCaps = (event) => {
                    if (typeof event.getModifierState === 'function') {
                        capsWarning.classList.toggle('hidden', !event.getModifierState('CapsLock'));
                    }
                };
                passwordInput.addEventListener('keyup', checkCaps);
                passwordInput.addEventListener('keydown', checkCaps);
            }
        })();
    </script>
@endsection
