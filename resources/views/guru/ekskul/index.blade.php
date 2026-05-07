{{-- resources/views/guru/ekskul/index.blade.php --}}
@extends('layouts.guru')

@section('title','Ekskul Saya')

@section('content')
  @php
    $tahunLabel = $tahunAktif->nama ?? $tahunAktif->tahun_ajaran ?? null;
    $tahunLabel = is_string($tahunLabel) ? trim($tahunLabel) : $tahunLabel;
  @endphp

  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Ekskul Saya</h1>

      <p class="text-sm text-gray-500">
        Daftar ekskul yang Anda bina sebagai pembina.
        @if(!empty($tahunLabel))

        @endif
      </p>
    </div>
  </div>

  @if (session('ok'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if (session('err'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-700">
      {{ session('err') }}
    </div>
  @endif

  <div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
          <th class="px-4 py-3 text-left">Nama Ekskul</th>
          <th class="px-4 py-3 text-left">Hari</th>
          <th class="px-4 py-3 text-left">Jam</th>
          <th class="px-4 py-3 text-left">Lokasi</th>
          <th class="px-4 py-3 text-center">Anggota</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        @forelse($items as $ekskul)
          <tr>
            <td class="px-4 py-3 font-medium text-gray-900">{{ $ekskul->nama }}</td>

            <td class="px-4 py-3">{{ $ekskul->hari ?? '-' }}</td>

            <td class="px-4 py-3">
              @if($ekskul->jam_mulai || $ekskul->jam_selesai)
                {{ $ekskul->jam_mulai ? \Illuminate\Support\Str::substr($ekskul->jam_mulai, 0, 5) : '–' }}
                -
                {{ $ekskul->jam_selesai ? \Illuminate\Support\Str::substr($ekskul->jam_selesai, 0, 5) : '–' }}
              @else
                -
              @endif
            </td>

            <td class="px-4 py-3">{{ $ekskul->lokasi ?? '-' }}</td>

            <td class="px-4 py-3 text-center">{{ $ekskul->anggota_aktif_count ?? 0 }}</td>

            <td class="px-4 py-3">
              <div class="flex justify-end gap-2">
                <a href="{{ route('guru.ekskul.anggota', $ekskul) }}"
                   class="px-3 py-1.5 rounded-full border text-xs text-gray-700 hover:bg-gray-50">
                  Anggota
                </a>
                <a href="{{ route('guru.ekskul.presensi', $ekskul) }}"
                   class="px-3 py-1.5 rounded-full border text-xs text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100">
                  Presensi
                </a>
                <a href="{{ route('guru.ekskul.penilaian', $ekskul) }}"
                   class="px-3 py-1.5 rounded-full border text-xs text-emerald-700 border-emerald-200 bg-emerald-50 hover:bg-emerald-100">
                  Penilaian
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
              Anda belum terdaftar sebagai pembina ekskul.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
