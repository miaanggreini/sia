<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – Sistem Informasi Akademik</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased overflow-x-hidden">

<header class="bg-white shadow fixed inset-x-0 top-0 z-30">
  <div class="h-16 w-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
    {{-- Logo + Judul --}}
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/logo-sman2.png') }}"
           alt="Logo SMA N 2 Temanggung"
           class="h-10 w-10 object-contain">
      <span class="font-semibold text-lg text-gray-800">
        Sistem Informasi Akademik
      </span>
    </div>

    <div></div>
  </div>
</header>

{{-- WRAPPER: Sidebar kiri + Konten kanan --}}
<div class="pt-16 flex min-h-screen overflow-x-hidden">
  {{-- SIDEBAR --}}
  <aside class="w-64 bg-white border-r h-[calc(100vh-4rem)] sticky top-16 overflow-y-auto">
    <div class="px-4 py-3 font-semibold border-b"></div>

    <nav class="p-3">
      <div class="px-2 py-2 text-[11px] tracking-wider text-gray-500">MASTER DATA</div>
      <ul class="space-y-1">

        {{-- DASHBOARD --}}
        <li>
          <a href="{{ route('admin.dashboard') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Dashboard
          </a>
        </li>

        {{-- GURU --}}
        <li>
          <a href="{{ route('admin.guru.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.guru.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Guru
          </a>
        </li>

        {{-- SISWA --}}
        <li>
          <a href="{{ route('admin.siswa.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.siswa.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Siswa
          </a>
        </li>

        {{-- ROMBEL (kelas belajar) --}}
        <li>
          <a href="{{ route('admin.rombel.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.rombel.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Rombel
          </a>
        </li>

        {{-- RUANG KELAS (fisik) --}}
        <li>
          <a href="{{ route('admin.ruang-kelas.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.ruang-kelas.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Ruang Kelas
          </a>
        </li>

        {{-- MATA PELAJARAN --}}
        <li>
          <a href="{{ route('admin.mapel.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.mapel.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Mata Pelajaran
          </a>
        </li>

        {{-- TAHUN AJARAN --}}
        <li>
          <a href="{{ route('admin.tahun_ajaran.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.tahun_ajaran.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Tahun Ajaran
          </a>
        </li>

        {{-- JADWAL --}}
        <li>
          <a href="{{ route('admin.jadwal.index') }}"
             class="block px-3 py-2 rounded-md text-sm
             {{ request()->routeIs('admin.jadwal.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
            Jadwal
          </a>
        </li>

      </ul>
    </nav>
  </aside>

  {{-- KONTEN --}}
  <main class="flex-1 p-6 overflow-x-auto md:overflow-x-visible">
    @yield('content')
  </main>
</div>

</body>
</html>
