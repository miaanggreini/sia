@extends('layouts.admin')

@section('title','Periode Pemilihan Menu XI')

@section('content')
@php
  \Carbon\Carbon::setLocale('id');

  $totalPeriode = $items->count();
  $sedangDibuka = $items->where('status', 'dibuka')->count();

  // Status terkunci tetap dihitung sebagai sudah ditempatkan/selesai.
  $sudahDitempatkan = $items->whereIn('status', ['ditempatkan', 'terkunci'])->count();
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        Periode Pemilihan Rombel
      </h1>

      <p class="mt-1 text-sm text-gray-500">
        Kelola periode pemilihan menu rombel untuk siswa kelas XI.
      </p>
    </div>

    <a href="{{ route('admin.periode.create') }}"
       class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
      <span class="text-lg leading-none">+</span>
      Buat Periode
    </a>
  </div>

  {{-- Alert --}}
  @if(session('ok'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('err'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
      {{ session('err') }}
    </div>
  @endif

  {{-- Info Cards --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">
        Total Periode
      </div>

      <div class="mt-2 text-2xl font-bold text-gray-800">
        {{ $totalPeriode }}
      </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">
        Sedang Dibuka
      </div>

      <div class="mt-2 text-2xl font-bold text-green-600">
        {{ $sedangDibuka }}
      </div>
    </div>

    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="text-sm text-gray-500">
        Sudah Ditempatkan
      </div>

      <div class="mt-2 text-2xl font-bold text-blue-600">
        {{ $sudahDitempatkan }}
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-800">
        Daftar Periode
      </h2>

      <p class="mt-1 text-sm text-gray-500">
        Gunakan aksi di setiap periode untuk membuka, menutup, dan melihat hasil penempatan.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-5 py-3 text-left">
              Nama Periode
            </th>

            <th class="px-5 py-3 text-left">
              Waktu Pelaksanaan
            </th>

            <th class="px-5 py-3 text-center">
              Status
            </th>

            <th class="px-5 py-3 text-right">
              Aksi
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($items as $it)
            @php
              $status = strtolower((string) $it->status);

              $badgeClass = match($status) {
                'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                'dibuka' => 'bg-green-100 text-green-700 border-green-200',
                'ditutup' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                'ditempatkan' => 'bg-blue-100 text-blue-700 border-blue-200',
                'terkunci' => 'bg-blue-100 text-blue-700 border-blue-200',
                default => 'bg-gray-100 text-gray-700 border-gray-200',
              };

              /*
               * Status terkunci tidak ditampilkan sebagai "Terkunci"
               * supaya tidak membingungkan admin.
               * Dalam alur ini, terkunci berarti hasil sudah selesai/commit.
               */
              $statusLabel = match($status) {
                'draft' => 'Draft',
                'dibuka' => 'Dibuka',
                'ditutup' => 'Ditutup',
                'ditempatkan' => 'Ditempatkan',
                'terkunci' => 'Ditempatkan',
                default => strtoupper((string) $it->status),
              };

              $tanggalMulai = $it->tanggal_mulai
                ? \Carbon\Carbon::parse($it->tanggal_mulai)->locale('id')->translatedFormat('d M Y H:i')
                : '-';

              $tanggalSelesai = $it->tanggal_selesai
                ? \Carbon\Carbon::parse($it->tanggal_selesai)->locale('id')->translatedFormat('d M Y H:i')
                : '-';

              $tahunAjaranLabel = $it->tahunAjaran->nama_tahun
                ?? $it->tahunAjaran->nama
                ?? $it->tahunAjaran->tahun
                ?? '-';
            @endphp

            <tr class="transition hover:bg-gray-50">
              <td class="px-5 py-4 align-top">
                <div class="font-semibold text-gray-800">
                  {{ $it->nama_periode }}
                </div>

                <div class="mt-1 text-xs text-gray-500">
                  Periode pemilihan menu rombel kelas {{ $it->tingkat ?? 'XI' }}
                </div>

                <div class="mt-1 text-xs text-gray-400">
                  Tahun ajaran: {{ $tahunAjaranLabel }}
                </div>
              </td>

              <td class="px-5 py-4 align-top">
                <div class="flex flex-col gap-1 text-gray-700">
                  <div>
                    <span class="text-xs text-gray-400">
                      Mulai
                    </span>

                    <span class="ml-2 font-medium">
                      {{ $tanggalMulai }}
                    </span>
                  </div>

                  <div>
                    <span class="text-xs text-gray-400">
                      Selesai
                    </span>

                    <span class="ml-2 font-medium">
                      {{ $tanggalSelesai }}
                    </span>
                  </div>
                </div>
              </td>

              <td class="px-5 py-4 text-center align-top">
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                  {{ $statusLabel }}
                </span>
              </td>

              <td class="px-5 py-4 align-top">
                <div class="flex flex-nowrap justify-end gap-2 whitespace-nowrap">

                  {{-- Draft -> Buka --}}
                  @if($status === 'draft')
                    <form method="POST" action="{{ route('admin.periode.buka', $it) }}">
                      @csrf

                      <button type="submit"
                              class="inline-flex items-center gap-1 rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-100">
                        Buka
                      </button>
                    </form>
                  @endif

                  {{-- Dibuka -> Tutup --}}
                  @if($status === 'dibuka')
                    <form method="POST" action="{{ route('admin.periode.tutup', $it) }}">
                      @csrf

                      <button type="submit"
                              class="inline-flex items-center gap-1 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-700 transition hover:bg-yellow-100">
                        Tutup
                      </button>
                    </form>
                  @endif

                  {{-- Ditutup / Ditempatkan / Terkunci -> Hasil --}}
                  @if(in_array($status, ['ditutup', 'ditempatkan', 'terkunci'], true))
                    <a href="{{ route('admin.periode.hasil', $it) }}"
                       class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                      Hasil
                    </a>
                  @endif

                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-12 text-center">
                <div class="mx-auto max-w-sm">
                  <div class="text-base font-semibold text-gray-700">
                    Belum ada periode pemilihan
                  </div>

                  <p class="mt-1 text-sm text-gray-500">
                    Buat periode baru agar siswa kelas XI dapat memilih menu rombel.
                  </p>

                  <a href="{{ route('admin.periode.create') }}"
                     class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Buat Periode
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection