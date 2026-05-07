{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    {{-- Alpine core + collapse --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIA - Admin')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body
    class="bg-gray-100 font-sans antialiased"
    x-data="{
        sidebarOpen: false,
        profileOpen: false,
        open: {
            master: {{ request()->routeIs('admin.guru.*','admin.siswa.*','admin.ruang-kelas.*','admin.mapel.*','admin.ekskul.*','admin.rombel.*') ? 'true' : 'false' }},
            ops: {{ request()->routeIs('admin.jadwal.*','admin.presensi.*','admin.penilaian.*') ? 'true' : 'false' }},
            penjurusan: {{ request()->routeIs('admin.menu-rombel.*','admin.periode.*','admin.pemilihan.rekap') ? 'true' : 'false' }},
            info: {{ request()->routeIs('admin.pengumuman.*','admin.tahun_ajaran.*') ? 'true' : 'false' }},
        },
        toggle(key) {
            this.open[key] = !this.open[key];
            localStorage.setItem('adminNavOpen', JSON.stringify(this.open));
        },
        closeMobileSidebar() {
            this.sidebarOpen = false;
        },
        init() {
            const saved = localStorage.getItem('adminNavOpen');
            if (saved) {
                try {
                    this.open = { ...this.open, ...JSON.parse(saved) };
                } catch (e) {}
            }
        }
    }"
    x-init="init()"
>

@php
    $userName = auth()->user()->name ?? 'Admin';
    $initial = strtoupper(substr($userName, 0, 1));

    $navItem = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition duration-150';
    $navActive = 'bg-indigo-100 text-indigo-700 font-semibold';
    $navNormal = 'text-gray-600 hover:bg-gray-50 hover:text-indigo-700';

    $childItem = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition duration-150';
    $childActive = 'bg-indigo-100 text-indigo-700 font-semibold';
    $childNormal = 'text-gray-600 hover:bg-gray-50 hover:text-indigo-700';
@endphp

{{-- HEADER --}}
<header class="fixed inset-x-0 top-0 z-50 h-16 border-b border-gray-200 bg-white shadow-sm">
    <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">

        <div class="flex min-w-0 items-center gap-3">
            {{-- Hamburger hanya mobile/tablet --}}
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 lg:hidden"
                @click="sidebarOpen = true"
                aria-label="Buka menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <img src="{{ asset('images/logo-sman2.png') }}" class="h-11 w-11 shrink-0 object-contain" alt="Logo">

            <div class="min-w-0 leading-tight">
                <div class="truncate text-sm font-bold text-gray-900 sm:text-base">
                    Sistem Informasi Akademik
                </div>
                <div class="truncate text-xs text-gray-500">
                    SMA Negeri 2 Temanggung
                </div>
            </div>
        </div>

        {{-- PROFIL --}}
        <div class="relative">
            <button
                type="button"
                class="flex items-center gap-2 rounded-xl px-2 py-2 transition hover:bg-gray-100 sm:px-3"
                @click="profileOpen = !profileOpen"
            >
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                    {{ $initial }}
                </div>

                <span class="hidden max-w-[180px] truncate text-sm font-semibold text-gray-700 sm:inline">
                    {{ $userName }}
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div
                x-cloak
                x-show="profileOpen"
                x-transition
                @click.outside="profileOpen = false"
                class="absolute right-0 mt-2 w-44 rounded-xl border border-gray-100 bg-white py-2 shadow-xl"
            >
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- OVERLAY MOBILE --}}
<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-black/40 lg:hidden"
    @click="sidebarOpen = false"
></div>

{{-- SIDEBAR --}}
<aside
    class="fixed left-0 top-16 bottom-0 z-40 w-72 max-w-[85vw] -translate-x-full border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-sm"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    <div class="flex h-full flex-col">

        {{-- Header sidebar mobile --}}
        <div class="flex items-center justify-between border-b px-4 py-3 lg:hidden">
            <div class="text-sm font-semibold text-gray-800">
                Menu Admin
            </div>

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100"
                @click="sidebarOpen = false"
                aria-label="Tutup menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto p-4">

            {{-- DASHBOARD --}}
            <a
                href="{{ route('admin.dashboard') }}"
                @click="closeMobileSidebar()"
                class="{{ $navItem }} {{ request()->routeIs('admin.dashboard') ? $navActive : $navNormal }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <hr class="my-2 border-gray-100">

            {{-- MASTER DATA --}}
            <div>
                <button
                    type="button"
                    @click="toggle('master')"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition duration-150
                        {{ request()->routeIs('admin.guru.*','admin.siswa.*','admin.ruang-kelas.*','admin.mapel.*','admin.ekskul.*','admin.rombel.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-700' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span>Master Data</span>
                    </span>

                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.master ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="open.master" x-collapse.duration.250ms class="mt-1 space-y-1 pl-4">
                    <a href="{{ route('admin.guru.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.guru.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3H5v-3a4 4 0 014-4h6a4 4 0 014 4v3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                        <span>Guru</span>
                    </a>

                    <a href="{{ route('admin.siswa.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.siswa.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.825-2.715 12.083 12.083 0 01.665-6.479L12 14z"/>
                        </svg>
                        <span>Siswa</span>
                    </a>

                    <a href="{{ route('admin.ruang-kelas.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.ruang-kelas.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-2M5 21h2m0 0h10M5 5h14M12 8v4m0 0v4m0-4h4m-4 0H8"/>
                        </svg>
                        <span>Ruang Kelas</span>
                    </a>

                    <a href="{{ route('admin.rombel.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.rombel.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>Rombel</span>
                    </a>

                    <a href="{{ route('admin.mapel.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.mapel.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Mata Pelajaran</span>
                    </a>

                    <a href="{{ route('admin.ekskul.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.ekskul.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a2 2 0 01-2-2v-1a2 2 0 012-2h10a2 2 0 012 2v1a2 2 0 01-2 2H7z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4a2 2 0 012-2h6a2 2 0 012 2v12"/>
                        </svg>
                        <span>Ekskul</span>
                    </a>
                </div>
            </div>

            <hr class="my-2 border-gray-100">

            {{-- OPERASIONAL AKADEMIK --}}
            <div>
                <button
                    type="button"
                    @click="toggle('ops')"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition duration-150
                        {{ request()->routeIs('admin.jadwal.*','admin.presensi.*','admin.penilaian.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-700' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Operasional Akademik</span>
                    </span>

                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.ops ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="open.ops" x-collapse.duration.250ms class="mt-1 space-y-1 pl-4">
                    <a href="{{ route('admin.jadwal.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.jadwal.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-4 14v-4m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Jadwal</span>
                    </a>

                    <a href="{{ route('admin.presensi.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.presensi.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-4 17a2 2 0 01-2-2h4a2 2 0 01-2 2zM3 7h18a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V8a1 1 0 011-1z"/>
                        </svg>
                        <span>Presensi</span>
                    </a>

                    <a href="{{ route('admin.penilaian.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.penilaian.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-8m3 8v-8m3 8V7m-9 12h12a1 1 0 001-1V6a1 1 0 00-1-1H7a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                        </svg>
                        <span>Penilaian</span>
                    </a>
                </div>
            </div>

            <hr class="my-2 border-gray-100">

            {{-- PEMILIHAN ROMBEL --}}
            <div>
                <button
                    type="button"
                    @click="toggle('penjurusan')"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition duration-150
                        {{ request()->routeIs('admin.menu-rombel.*','admin.periode.*','admin.pemilihan.rekap') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-700' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span>Pemilihan Rombel</span>
                    </span>

                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.penjurusan ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="open.penjurusan" x-collapse.duration.250ms class="mt-1 space-y-1 pl-4">
                    <a href="{{ route('admin.menu-rombel.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.menu-rombel.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Menu Rombel</span>
                    </a>

                    <a href="{{ route('admin.periode.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.periode.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Periode Pemilihan</span>
                    </a>

                    <a href="{{ route('admin.pemilihan.rekap') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.pemilihan.rekap') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        <span>Rekap & Penempatan</span>
                    </a>
                </div>
            </div>

            <hr class="my-2 border-gray-100">

            {{-- PUSAT INFORMASI --}}
            <div>
                <button
                    type="button"
                    @click="toggle('info')"
                    class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm transition duration-150
                        {{ request()->routeIs('admin.pengumuman.*','admin.tahun_ajaran.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-700' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Pusat Informasi</span>
                    </span>

                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open.info ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div x-show="open.info" x-collapse.duration.250ms class="mt-1 space-y-1 pl-4">
                    <a href="{{ route('admin.pengumuman.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.pengumuman.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Pengumuman</span>
                    </a>

                    <a href="{{ route('admin.tahun_ajaran.index') }}" @click="closeMobileSidebar()" class="{{ $childItem }} {{ request()->routeIs('admin.tahun_ajaran.*') ? $childActive : $childNormal }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-4 14v-4m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Tahun Ajaran</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>
</aside>

{{-- FLASH MESSAGE --}}
@if (session('ok') || session('err'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 3500)"
        class="fixed right-4 top-20 z-[60] w-[calc(100%-2rem)] max-w-md sm:right-6"
    >
        <div class="rounded-xl border px-4 py-3 shadow-lg
            {{ session('ok') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white
                        {{ session('ok') ? 'bg-emerald-600' : 'bg-red-600' }}">
                        {{ session('ok') ? '✓' : '!' }}
                    </div>

                    <div class="text-sm font-medium leading-relaxed">
                        {{ session('ok') ?? session('err') }}
                    </div>
                </div>

                <button type="button" class="opacity-60 hover:opacity-100" @click="show = false" aria-label="Tutup">
                    ×
                </button>
            </div>
        </div>
    </div>
@endif

{{-- KONTEN --}}
<main class="min-h-screen pt-16 lg:pl-64">
    <div class="w-full p-4 sm:p-5 lg:p-6">
        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>