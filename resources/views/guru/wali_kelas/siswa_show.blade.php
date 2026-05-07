@extends('layouts.guru')

@section('content')
  {{-- HEADER --}}
  <div class="mb-5 flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold leading-tight">
        Profil Siswa — {{ $siswa->nama }}
      </h1>
    </div>

<a href="{{ route('guru.wali.kelas-saya') }}"
   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-200 shadow-sm
          text-indigo-700 hover:bg-indigo-50 hover:border-indigo-200
          focus:outline-none focus:ring-2 focus:ring-indigo-500/30
          active:translate-y-[1px] transition">
  {{-- Panah hanya dari SVG ini, tidak ada karakter "←" di text --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
    <path fill-rule="evenodd" d="M12.78 15.53a.75.75 0 01-1.06 0l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 111.06 1.06L8.31 9l4.47 4.47a.75.75 0 010 1.06z" clip-rule="evenodd"/>
  </svg>
  <span>Kembali</span>
</a>
  </div>

  {{-- KARTU PROFIL --}}
  <div class="bg-white rounded-xl shadow border overflow-hidden">
    {{-- Header Kartu --}}
    <div class="p-5 border-b">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-semibold">
          {{ strtoupper(substr($siswa->nama,0,1)) }}
        </div>
        <div class="min-w-0">
          <div class="text-lg font-semibold text-gray-900 truncate">
            {{ $siswa->nama }}
          </div>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
              NIS: {{ $siswa->nis ?? '—' }}
            </span>
            @if($siswa->nisn)
              <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                NISN: {{ $siswa->nisn }}
              </span>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Isi Kartu --}}
    <div class="p-5 grid md:grid-cols-2 gap-6 text-sm">
      {{-- Kolom Kiri --}}
      <div class="space-y-4">
        <div>
          <div class="text-gray-500">Jenis Kelamin</div>
          <div class="mt-1">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs border
              {{ $siswa->jk === 'L'
                  ? 'bg-blue-50 text-blue-700 border-blue-200'
                  : 'bg-pink-50 text-pink-700 border-pink-200' }}">
              {{ $siswa->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}
            </span>
          </div>
        </div>

        <div>
          <div class="text-gray-500">No HP</div>
          <div class="mt-1 font-medium text-gray-900">
            {{ $siswa->no_hp ?? '—' }}
          </div>
        </div>

        <div>
          <div class="text-gray-500">Alamat</div>
          <div class="mt-1 font-medium text-gray-900">
            {{ $siswa->alamat ?? '—' }}
          </div>
        </div>
      </div>

      {{-- Kolom Kanan --}}
      <div class="space-y-4">
        <div>
          <div class="text-gray-500">Tempat/Tgl Lahir</div>
          <div class="mt-1 font-medium text-gray-900">
            {{ ($siswa->tempat_lahir ?? '—').', '.($siswa->tanggal_lahir?->translatedFormat('d M Y') ?? '—') }}
          </div>
        </div>

        <div>
          <div class="text-gray-500">Email</div>
          <div class="mt-1 font-medium text-gray-900">
            {{ $siswa->email ?? '—' }}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
