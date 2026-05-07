@extends('layouts.siswa')

@section('title','Profil Saya')

@section('content')
@php
  $fotoPath = $siswa->foto
      ? asset('storage/'.ltrim($siswa->foto,'/'))
      : 'https://via.placeholder.com/160x160?text=Foto';

  $tglLahir = $siswa->tanggal_lahir
      ? \Illuminate\Support\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d M Y')
      : null;
@endphp

<div
  x-data="{
    tab: localStorage.getItem('tabProfilSiswa') || 'pribadi',
    setTab(t){ this.tab = t; localStorage.setItem('tabProfilSiswa', t); }
  }"
  class="bg-white rounded-2xl shadow border overflow-hidden"
>
  {{-- HEADER --}}
  <div class="px-6 py-4 border-b flex flex-col gap-3 md:flex-row md:items-center md:justify-between bg-gradient-to-r from-indigo-50 to-sky-50">
    <div class="flex items-center gap-3">
      <img src="{{ $fotoPath }}" class="h-14 w-14 rounded-full object-cover border" alt="Foto {{ $siswa->nama }}">

      <div>
        <div class="text-lg font-semibold text-gray-900">
          {{ $siswa->nama }}
        </div>
        <div class="text-xs text-gray-500">
          NIS: <span class="font-medium text-gray-800">{{ $siswa->nis ?? '-' }}</span>
          @if($siswa->status === 'aktif')
            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px]">
              Aktif
            </span>
          @else
            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px]">
              {{ ucfirst($siswa->status ?? 'nonaktif') }}
            </span>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TAB NAV --}}
  <div class="flex flex-wrap bg-indigo-600 text-white text-sm font-medium">
    @php
      $tabs = [
        'pribadi' => 'Data Pribadi',
        'kontak'  => 'Kontak & Alamat',
        'akademik'=> 'Akademik',
        'ayah'    => 'Data Ayah',
        'ibu'     => 'Data Ibu',
      ];
    @endphp

    @foreach($tabs as $key => $label)
      <button
        type="button"
        @click="setTab('{{ $key }}')"
        :class="tab === '{{ $key }}'
            ? 'bg-white text-indigo-700 shadow-inner'
            : 'text-white hover:bg-indigo-500/40'"
        class="px-5 py-3 transition-all duration-150 border-r border-indigo-500 last:border-0"
      >
        {{ $label }}
      </button>
    @endforeach
  </div>

  {{-- TAB CONTENT --}}
  <div class="p-6 bg-gray-50 space-y-6 text-sm">

    {{-- ===================== DATA PRIBADI ===================== --}}
    <section x-show="tab==='pribadi'" x-transition x-cloak class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Data Pribadi</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
        {{-- Foto --}}
        <div class="space-y-3">
          <div class="text-xs font-semibold text-gray-600 uppercase">Foto Siswa</div>
          <div class="p-3 border border-blue-200 rounded-lg bg-white shadow-sm">
            <img src="{{ $fotoPath }}" class="h-40 w-40 object-cover rounded-md border border-blue-100 shadow" alt="Foto Siswa">
          </div>
        </div>

        {{-- Detail --}}
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-xs text-gray-500">Nama Lengkap</div>
            <div class="font-medium text-gray-900">{{ $siswa->nama ?? '-' }}</div>
          </div>

          <div>
            <div class="text-xs text-gray-500">Tempat Lahir</div>
            <div class="font-medium text-gray-900">{{ $siswa->tempat_lahir ?? '-' }}</div>
          </div>

          <div>
            <div class="text-xs text-gray-500">Jenis Kelamin</div>
            <div class="font-medium text-gray-900">
              @if($siswa->jenis_kelamin === 'L') Laki-laki
              @elseif($siswa->jenis_kelamin === 'P') Perempuan
              @else - @endif
            </div>
          </div>

          <div>
            <div class="text-xs text-gray-500">Tanggal Lahir</div>
            <div class="font-medium text-gray-900">{{ $tglLahir ?? '-' }}</div>
          </div>

          <div>
            <div class="text-xs text-gray-500">NISN</div>
            <div class="font-medium text-gray-900">{{ $siswa->nisn ?? '-' }}</div>
          </div>

          <div>
            <div class="text-xs text-gray-500">NIS</div>
            <div class="font-medium text-gray-900">{{ $siswa->nis ?? '-' }}</div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== KONTAK & ALAMAT ===================== --}}
    <section x-show="tab==='kontak'" x-transition x-cloak class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Kontak & Alamat</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-xs text-gray-500">Agama</div>
          <div class="font-medium text-gray-900">{{ $siswa->agama ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Nomor HP</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->no_hp ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Email</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->email ?? '-' }}</div>
        </div>

        <div class="md:col-span-3">
          <div class="text-xs text-gray-500">Alamat Lengkap</div>
          <div class="font-medium text-gray-900 whitespace-pre-line">
            {{ $siswa->alamat ?? '-' }}
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== AKADEMIK ===================== --}}
    <section x-show="tab==='akademik'" x-transition x-cloak class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Akademik</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-xs text-gray-500">Jalur Penerimaan</div>
          <div class="font-medium text-gray-900">{{ $siswa->jalur_penerimaan ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Kebutuhan Khusus</div>
          <div class="font-medium text-gray-900">{{ $siswa->kebutuhan_khusus ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Tahun Masuk</div>
          <div class="font-medium text-gray-900">{{ $siswa->tahun_masuk ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Status</div>
          <div class="font-medium text-gray-900">
            {{ $siswa->status ? ucfirst($siswa->status) : '-' }}
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== DATA AYAH ===================== --}}
    <section x-show="tab==='ayah'" x-transition x-cloak class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Data Ayah</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-xs text-gray-500">Nama Ayah</div>
          <div class="font-medium text-gray-900">{{ $siswa->nama_ayah ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">NIK Ayah</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->nik_ayah ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Status Ayah</div>
          <div class="font-medium text-gray-900">{{ $siswa->status_ayah ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Pekerjaan Ayah</div>
          <div class="font-medium text-gray-900">{{ $siswa->pekerjaan_ayah ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Pendidikan Ayah</div>
          <div class="font-medium text-gray-900">{{ $siswa->pendidikan_ayah ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">No HP Ayah</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->no_hp_ayah ?? '-' }}</div>
        </div>

        <div class="md:col-span-3">
          <div class="text-xs text-gray-500">Alamat Ayah</div>
          <div class="font-medium text-gray-900 whitespace-pre-line">
            {{ $siswa->alamat_ayah ?? '-' }}
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== DATA IBU ===================== --}}
    <section x-show="tab==='ibu'" x-transition x-cloak class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Data Ibu</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-xs text-gray-500">Nama Ibu</div>
          <div class="font-medium text-gray-900">{{ $siswa->nama_ibu ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">NIK Ibu</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->nik_ibu ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Status Ibu</div>
          <div class="font-medium text-gray-900">{{ $siswa->status_ibu ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Pekerjaan Ibu</div>
          <div class="font-medium text-gray-900">{{ $siswa->pekerjaan_ibu ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">Pendidikan Ibu</div>
          <div class="font-medium text-gray-900">{{ $siswa->pendidikan_ibu ?? '-' }}</div>
        </div>

        <div>
          <div class="text-xs text-gray-500">No HP Ibu</div>
          <div class="font-medium text-gray-900 break-all">{{ $siswa->no_hp_ibu ?? '-' }}</div>
        </div>

        <div class="md:col-span-3">
          <div class="text-xs text-gray-500">Alamat Ibu</div>
          <div class="font-medium text-gray-900 whitespace-pre-line">
            {{ $siswa->alamat_ibu ?? '-' }}
          </div>
        </div>
      </div>
    </section>

  </div>
</div>
@endsection
