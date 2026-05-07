@extends('layouts.kepsek')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-semibold">Detail Guru</h1>

        <a href="{{ route('kepala_sekolah.data.guru') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-600
                  text-indigo-700 font-medium bg-white hover:bg-indigo-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow border p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="border rounded-2xl p-4 bg-gray-50 h-full">
                    <div class="flex justify-center">
                        <img src="{{ $guru->foto_url }}"
                             alt="Foto {{ $guru->nama }}"
                             class="w-64 h-80 object-cover rounded-xl border shadow-sm">
                    </div>

                    <div class="mt-5 text-center">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $guru->nama }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $guru->status_kepegawaian ?: '-' }}</p>

                        <div class="mt-3">
                            @if($guru->status === 'aktif')
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="space-y-8">
                    <div>
                        <h2 class="text-xl font-semibold text-indigo-700 mb-4">Data Pribadi</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Nama Lengkap</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->nama ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Jenis Kelamin</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->jk_label }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">NIP</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->nip ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">NUPTK</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->nuptk ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Tempat Lahir</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->tempat_lahir ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Tanggal Lahir</p>
                                <p class="mt-2 font-semibold text-gray-900">
                                    {{ $guru->tanggal_lahir ? \Carbon\Carbon::parse($guru->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-indigo-700 mb-4">Kepegawaian & Kontak</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Status Kepegawaian</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->status_kepegawaian ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">No. HP</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->no_hp ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4 md:col-span-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Email</p>
                                <p class="mt-2 font-semibold text-gray-900">{{ $guru->email ?: '-' }}</p>
                            </div>

                            <div class="rounded-xl border bg-gray-50 p-4 md:col-span-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Alamat Lengkap</p>
                                <p class="mt-2 font-semibold text-gray-900 whitespace-pre-line">{{ $guru->alamat ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <a href="{{ route('kepala_sekolah.data.guru.edit', $guru) }}"
                           class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Edit Data
                        </a>
                        <a href="{{ route('kepala_sekolah.data.guru') }}"
                           class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection