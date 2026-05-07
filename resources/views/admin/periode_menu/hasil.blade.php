@extends('layouts.admin')
@section('title','Hasil Penempatan Menu')

@section('content')
@php
  use App\Models\SiswaMenuPeriode;

  $hasilSiswa = SiswaMenuPeriode::with([
      'siswa',
      'pilihan1',
      'pilihan2',
      'menuDiterima',
  ])
      ->where('periode_id', $periode->id)
      ->orderBy('menu_diterima_id')
      ->orderBy('id')
      ->get();

  $siswaDiterima = $hasilSiswa->whereNotNull('menu_diterima_id');
  $siswaBelum = $hasilSiswa->whereNull('menu_diterima_id');

  $totalSiswa = $hasilSiswa->count();
  $totalTerpasang = $siswaDiterima->count();
  $belum = $siswaBelum->count();

  $persenTerpasang = $totalSiswa > 0 ? round(($totalTerpasang / $totalSiswa) * 100, 1) : 0;

  $groupMenu = $siswaDiterima->groupBy('menu_diterima_id');
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Hasil Penempatan Menu Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Periode:
        <span class="font-semibold text-gray-700">{{ $periode->nama_periode }}</span>
      </p>
    </div>

    <a href="{{ route('admin.periode.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
      Kembali
    </a>
  </div>

  {{-- Summary --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">Total Pendaftar</div>
      <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalSiswa }}</div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">Sudah Ditempatkan</div>
      <div class="mt-2 text-2xl font-bold text-blue-600">{{ $totalTerpasang }}</div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">Belum Ditempatkan</div>
      <div class="mt-2 text-2xl font-bold {{ $belum > 0 ? 'text-amber-600' : 'text-green-600' }}">
        {{ $belum }}
      </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">Progress</div>
      <div class="mt-2 text-2xl font-bold text-green-600">{{ $persenTerpasang }}%</div>
    </div>
  </div>



  {{-- Distribusi dan Detail digabung --}}
  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-900">Distribusi & Detail Siswa per Menu</h2>
      <p class="mt-1 text-sm text-gray-500">
        Menampilkan jumlah siswa sekaligus daftar siswa yang diterima pada masing-masing menu rombel.
      </p>
    </div>

    <div class="divide-y divide-gray-100">
      @forelse($groupMenu as $menuId => $items)
        @php
          $menu = $items->first()?->menuDiterima;
          $jumlah = $items->count();
          $persenMenu = $totalTerpasang > 0 ? round(($jumlah / $totalTerpasang) * 100, 1) : 0;
        @endphp

        <div class="p-5">
          <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <h3 class="text-base font-bold text-gray-900">
                {{ $menu?->nama ?? 'Menu tidak ditemukan' }}
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                {{ $jumlah }} siswa diterima atau {{ $persenMenu }}% dari total siswa yang sudah ditempatkan.
              </p>
            </div>

            <div class="flex flex-wrap gap-2">
              <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ $jumlah }} siswa
              </span>
              <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                {{ $persenMenu }}%
              </span>
            </div>
          </div>

          <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                  <th class="px-4 py-3 text-left w-16">No</th>
                  <th class="px-4 py-3 text-left">Nama Siswa</th>
                  <th class="px-4 py-3 text-left">NIS</th>
                  <th class="px-4 py-3 text-left">Pilihan Utama</th>
                  <th class="px-4 py-3 text-left">Pilihan Cadangan</th>
                  <th class="px-4 py-3 text-center">Status Pilihan</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($items->values() as $i => $row)
                  @php
                    if ($row->menu_diterima_id == $row->pilihan_1_menu_id) {
                        $status = 'Pilihan Utama';
                        $badgeClass = 'bg-green-50 text-green-700 border-green-200';
                    } elseif ($row->menu_diterima_id == $row->pilihan_2_menu_id) {
                        $status = 'Pilihan Cadangan';
                        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    } else {
                        $status = 'Manual';
                        $badgeClass = 'bg-gray-50 text-gray-700 border-gray-200';
                    }
                  @endphp

                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>

                    <td class="px-4 py-3">
                      <div class="font-semibold text-gray-900">
                        {{ $row->siswa?->nama ?? '-' }}
                      </div>
                    </td>

                    <td class="px-4 py-3 text-gray-600">
                      {{ $row->siswa?->nis ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                      {{ $row->pilihan1?->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                      {{ $row->pilihan2?->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-center">
                      <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                        {{ $status }}
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-gray-500">
          Belum ada siswa yang ditempatkan.
        </div>
      @endforelse

      {{-- Belum ditempatkan tetap masuk dalam card yang sama --}}
      <div class="p-5 bg-gray-50">
        <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h3 class="text-base font-bold text-gray-900">Belum Ditempatkan</h3>
          </div>

          <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold
            {{ $belum > 0 ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">
            {{ $belum > 0 ? $belum.' siswa perlu dicek' : 'Semua siswa sudah ditempatkan' }}
          </span>
        </div>

        @if($siswaBelum->count() > 0)
          <div class="overflow-x-auto rounded-xl border border-amber-200 bg-white">
            <table class="min-w-full text-sm">
              <thead class="bg-amber-50 text-xs uppercase text-amber-700">
                <tr>
                  <th class="px-4 py-3 text-left w-16">No</th>
                  <th class="px-4 py-3 text-left">Nama Siswa</th>
                  <th class="px-4 py-3 text-left">NIS</th>
                  <th class="px-4 py-3 text-left">Pilihan Utama</th>
                  <th class="px-4 py-3 text-left">Pilihan Cadangan</th>
                  <th class="px-4 py-3 text-left">Keterangan</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100">
                @foreach($siswaBelum->values() as $i => $row)
                  <tr class="hover:bg-amber-50/40">
                    <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>

                    <td class="px-4 py-3 font-semibold text-gray-900">
                      {{ $row->siswa?->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-600">
                      {{ $row->siswa?->nis ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                      {{ $row->pilihan1?->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                      {{ $row->pilihan2?->nama ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-amber-700">
                      Belum mendapatkan kuota/menu diterima.
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            Semua siswa sudah berhasil ditempatkan.
          </div>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection