{{-- resources/views/guru/siswa/show.blade.php --}}
@extends('layouts.guru')

@section('title', 'Profil Siswa')

@section('content')
  {{-- BREADCRUMB & TOMBOL KEMBALI --}}
  <div class="mb-4 flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">
        Profil Siswa – {{ $siswa->nama }}
      </h1>
      <p class="text-sm text-gray-500">
        Informasi ringkas siswa untuk keperluan pengajaran, presensi, dan penilaian.
      </p>
    </div>

    <button type="button"
            onclick="window.history.back()"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-md border bg-white text-gray-700 text-sm hover:bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 111.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
      </svg>
      Kembali
    </button>
  </div>

  {{-- KARTU IDENTITAS SISWA --}}
  <div class="bg-white rounded-2xl border shadow-sm mb-4">
    <div class="px-4 py-3 border-b">
      <h2 class="text-sm font-semibold text-gray-800">Identitas Siswa</h2>
    </div>
    <div class="px-4 py-4 grid gap-4 md:grid-cols-2 text-sm">
      <div class="space-y-2">
        <div>
          <div class="text-xs text-gray-500">Nama lengkap</div>
          <div class="font-semibold text-gray-900">{{ $siswa->nama }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">NIS</div>
          <div class="text-gray-800">{{ $siswa->nis ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">NISN</div>
          <div class="text-gray-800">{{ $siswa->nisn ?? '-' }}</div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Jenis kelamin</div>
          <div class="text-gray-800">
            @switch($siswa->jenis_kelamin)
              @case('L') Laki-laki @break
              @case('P') Perempuan @break
              @default {{ $siswa->jenis_kelamin ?? '-' }}
            @endswitch
          </div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Tanggal lahir</div>
          <div class="text-gray-800">
            @if($siswa->tanggal_lahir)
              {{ \Illuminate\Support\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d M Y') }}
            @else
              -
            @endif
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <div>
          <div class="text-xs text-gray-500">Alamat</div>
          <div class="text-gray-800">
            {{ $siswa->alamat ?? '-' }}
          </div>
        </div>

        <div>
          <div class="text-xs text-gray-500">No. HP</div>
          <div class="text-gray-800">
            {{ $siswa->telepon ?? $siswa->no_hp ?? '-' }}
          </div>
        </div>

        {{-- Kalau kamu punya kolom lain (nama_orang_tua, dll) bisa ditambahkan di sini --}}
      </div>
    </div>
  </div>

  {{-- KARTU INFORMASI TAMBAHAN --}}
  @if($tahunAktif)
    <div class="bg-white rounded-2xl border shadow-sm mb-4">
      <div class="px-4 py-3 border-b flex items-center justify-between text-sm">
        <div>
          <h2 class="text-sm font-semibold text-gray-800">
            Kegiatan ekstrakurikuler tahun ajaran {{ $tahunAktif->nama ?? '-' }}
          </h2>
          <p class="text-xs text-gray-500">
            Daftar ekskul yang diikuti siswa pada tahun ajaran aktif.
          </p>
        </div>
      </div>

      <div class="px-4 py-2">
        @if($ekskulDiikuti->isEmpty())
          <p class="text-sm text-gray-500 py-3">
            Belum ada data ekskul yang diikuti pada tahun ajaran ini.
          </p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama ekskul</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pembina</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($ekskulDiikuti as $i => $row)
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-700">{{ $i + 1 }}</td>
                    <td class="px-4 py-2 text-gray-900 font-medium">
                      {{ $row->ekskul->nama ?? '-' }}
                    </td>
                    <td class="px-4 py-2 text-gray-700">
                      {{ $row->ekskul->pembina->nama ?? '-' }}
                    </td>
                    <td class="px-4 py-2">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $row->status === 'aktif'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($row->status) }}
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- OPSIONAL: info konteks mapel/jadwal --}}
  @if($jadwalId)
    <p class="mt-2 text-xs text-gray-400">
      Profil ini diakses dari konteks jadwal ID: {{ $jadwalId }} (informasi ini hanya untuk debugging / referensi, boleh dihapus nanti).
    </p>
  @endif
@endsection
