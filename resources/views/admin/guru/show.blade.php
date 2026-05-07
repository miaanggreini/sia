@extends('layouts.admin')

@section('content')
@php
  \Carbon\Carbon::setLocale('id');

  $fotoUrl = $guru->foto
    ? asset('storage/'.ltrim($guru->foto, '/'))
    : null;

  $jkText = $guru->jk === 'L'
    ? 'Laki-laki'
    : ($guru->jk === 'P' ? 'Perempuan' : '—');

  $statusAktif = $guru->status === 'aktif';

  $tglLahir = $guru->tanggal_lahir
    ? \Carbon\Carbon::parse($guru->tanggal_lahir)
        ->locale('id')
        ->translatedFormat('d F Y')
    : null;

  $createdAt = $guru->created_at
    ? $guru->created_at
        ->copy()
        ->timezone('Asia/Jakarta')
        ->locale('id')
        ->translatedFormat('d F Y • H:i')
    : '—';

  $updatedAt = $guru->updated_at
    ? $guru->updated_at
        ->copy()
        ->timezone('Asia/Jakarta')
        ->locale('id')
        ->translatedFormat('d F Y • H:i')
    : '—';
@endphp

<div class="w-full">
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

    {{-- Header --}}
    <div class="px-6 pt-10 pb-4">
      <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

        <div class="flex items-start gap-4">
          {{-- Foto / Avatar --}}
          <div class="shrink-0">
            @if($fotoUrl)
              <img src="{{ $fotoUrl }}"
                   alt="Foto {{ $guru->nama }}"
                   class="h-20 w-20 rounded-full border border-indigo-200 object-cover shadow-sm">
            @else
              <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600 text-2xl font-semibold text-white shadow-sm">
                {{ strtoupper(substr($guru->nama ?? 'G', 0, 1)) }}
              </div>
            @endif
          </div>

          <div class="min-w-0">
            <h1 class="mt-1 text-2xl font-bold leading-tight text-gray-900">
              {{ $guru->nama ?? '-' }}
            </h1>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
              <span class="text-gray-500">
                NIP:
                <span class="font-medium text-gray-700">
                  {{ $guru->nip ?: '—' }}
                </span>
              </span>

              @if($guru->status_kepegawaian)
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                  {{ $guru->status_kepegawaian }}
                </span>
              @endif

              <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                {{ $statusAktif ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                {{ $statusAktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
        </div>

        <a href="{{ route('admin.guru.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-indigo-600 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-4 w-4"
               fill="none"
               viewBox="0 0 24 24"
               stroke="currentColor"
               stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Kembali
        </a>
      </div>
    </div>

    <div class="px-6">
      <div class="border-t"></div>
    </div>

    {{-- Grid Detail --}}
    <div class="grid grid-cols-1 gap-4 px-6 py-6 md:grid-cols-2">

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">NUPTK</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $guru->nuptk ?: '—' }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Jenis Kelamin</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $jkText }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Tempat, Tanggal Lahir</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ trim(($guru->tempat_lahir ?? '').($tglLahir ? ', '.$tglLahir : '')) ?: '—' }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Status Kepegawaian</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $guru->status_kepegawaian ?: '—' }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">No HP</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $guru->no_hp ?: '—' }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Email</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $guru->email ?: '—' }}
        </p>
      </div>

      <div class="rounded-xl border bg-gray-50 p-4 md:col-span-2">
        <p class="text-xs uppercase tracking-wide text-gray-500">Alamat</p>
        <p class="mt-2 font-semibold text-gray-900">
          {{ $guru->alamat ?: '—' }}
        </p>
      </div>

    </div>
  </div>
</div>
@endsection