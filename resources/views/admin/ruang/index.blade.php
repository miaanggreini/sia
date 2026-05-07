@extends('layouts.admin')
@section('title','Ruang Kelas')

@section('content')
<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Ruang Kelas</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola daftar ruang kelas yang digunakan pada proses belajar mengajar.
      </p>
    </div>

    <a href="{{ route('admin.ruang-kelas.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
            </svg>
      Tambah Ruang
    </a>
  </div>

  {{-- SEARCH AUTO --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form id="searchRuangForm" method="GET" action="{{ route('admin.ruang-kelas.index') }}">
      <label class="mb-1 block text-sm font-medium text-gray-700">Cari Ruang</label>

      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
          </svg>
        </span>

        <input type="text"
               id="searchRuangInput"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari nama ruang..."
               autocomplete="off"
               class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        @if(request('q'))
          <a href="{{ route('admin.ruang-kelas.index') }}"
             class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-400 hover:text-red-500">
            ✕
          </a>
        @endif
      </div>

      <p class="mt-2 text-xs text-gray-400">
        Pencarian berjalan otomatis.
      </p>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    @if($items->isEmpty())
      <div class="px-5 py-12 text-center">
        <div class="flex flex-col items-center justify-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
            🏫
          </div>
          <h3 class="text-sm font-semibold text-gray-700">Belum ada ruang kelas</h3>
          <p class="mt-1 text-sm text-gray-500">
            Silakan tambahkan ruang kelas terlebih dahulu.
          </p>

          <a href="{{ route('admin.ruang-kelas.create') }}"
             class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            Tambah Ruang Pertama
          </a>
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Ruang</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kapasitas</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach($items as $i => $ruang)
              <tr class="hover:bg-indigo-50/30 transition">
                <td class="px-5 py-3 text-gray-600">
                  {{ $items->firstItem() + $i }}
                </td>

                <td class="px-5 py-3 font-semibold text-gray-800">
                  {{ $ruang->nama }}
                </td>

                <td class="px-5 py-3">
                  <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    {{ $ruang->kapasitas ?? '-' }} siswa
                  </span>
                </td>

                <td class="px-5 py-3 text-center">
                  <a href="{{ route('admin.ruang-kelas.edit', $ruang) }}"
                     class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                    Edit
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- PAGINATION --}}
      @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-5 py-3 border-t bg-gray-50">
          {{ $items->withQueryString()->links() }}
        </div>
      @endif
    @endif
  </div>

</div>

{{-- SCRIPT AUTO SEARCH --}}
<script>
  const searchInput = document.getElementById('searchRuangInput');
  const searchForm = document.getElementById('searchRuangForm');

  if (searchInput && searchForm) {
    let typingTimer;

    searchInput.addEventListener('input', function () {
      clearTimeout(typingTimer);

      typingTimer = setTimeout(function () {
        searchForm.submit();
      }, 500);
    });
  }
</script>
@endsection