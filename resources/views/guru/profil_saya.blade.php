@extends('layouts.guru')

@section('title','Profil Saya')

@section('content')
  <div class="space-y-6">

    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Profil Saya</h1>
      <p class="text-sm text-gray-500 mt-1">
        Data pribadi dan kepegawaian sebagai guru di SMA Negeri 2 Temanggung.
      </p>
    </div>

    @php
      $tglLahir = $guru->tanggal_lahir
          ? \Illuminate\Support\Carbon::parse($guru->tanggal_lahir)->translatedFormat('d M Y')
          : null;

      $fotoUrl = $guru->foto
          ? asset('storage/'.ltrim($guru->foto, '/'))
          : null;
    @endphp

    {{-- KARTU PROFIL --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">

      {{-- Header kartu --}}
      <div class="px-6 py-5 border-b flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
          {{-- Foto / avatar --}}
          @if($fotoUrl)
            <img src="{{ $fotoUrl }}"
                 alt="Foto {{ $guru->nama }}"
                 class="h-14 w-14 rounded-full object-cover border border-indigo-200">
          @else
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-400 to-sky-400
                        flex items-center justify-center text-white font-semibold text-xl">
              {{ strtoupper(substr($guru->nama, 0, 1)) }}
            </div>
          @endif

          <div>
            <div class="text-lg font-semibold text-gray-900">
              {{ $guru->nama }}
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-2">
              Guru • {{ $guru->status_kepegawaian ?? '-' }}
              @if($guru->status === 'aktif')
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px]">
                  Aktif
                </span>
              @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px]">
                  Nonaktif
                </span>
              @endif
            </div>
          </div>
        </div>

        
      </div>

      {{-- Isi kartu: 2 kolom --}}
      <div class="px-6 py-6 grid grid-cols-1 lg:grid-cols-2 gap-8 text-sm">

        {{-- Informasi Kepegawaian --}}
        <section class="space-y-4">
          <h2 class="text-xs font-semibold tracking-wide text-gray-500 uppercase">
            Informasi Kepegawaian
          </h2>

          <div class="space-y-3">
            <div>
              <div class="text-xs text-gray-500">NIP</div>
              <div class="font-medium text-gray-900">{{ $guru->nip ?? '-' }}</div>
            </div>

            <div>
              <div class="text-xs text-gray-500">NUPTK</div>
              <div class="font-medium text-gray-900">{{ $guru->nuptk ?? '-' }}</div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Status Kepegawaian</div>
              <div class="font-medium text-gray-900">{{ $guru->status_kepegawaian ?? '-' }}</div>
            </div>
          </div>
        </section>

        {{-- Informasi Pribadi & Kontak --}}
        <section class="space-y-4">
          <h2 class="text-xs font-semibold tracking-wide text-gray-500 uppercase">
            Informasi Pribadi &amp; Kontak
          </h2>

          <div class="space-y-3">
            <div>
              <div class="text-xs text-gray-500">Jenis Kelamin</div>
              <div class="font-medium text-gray-900">
                @if($guru->jk === 'L')
                  Laki-laki
                @elseif($guru->jk === 'P')
                  Perempuan
                @else
                  -
                @endif
              </div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Tempat, Tanggal Lahir</div>
              <div class="font-medium text-gray-900">
                {{ $guru->tempat_lahir ?? '-' }}{{ $tglLahir ? ', '.$tglLahir : '' }}
              </div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Email</div>
              <div class="font-medium text-gray-900">{{ $guru->email ?? '-' }}</div>
            </div>

            <div>
              <div class="text-xs text-gray-500">No. HP</div>
              <div class="font-medium text-gray-900">{{ $guru->no_hp ?? '-' }}</div>
            </div>

            <div>
              <div class="text-xs text-gray-500">Alamat</div>
              <div class="font-medium text-gray-900 whitespace-pre-line">
                {{ $guru->alamat ?? '-' }}
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>

  </div>
@endsection
