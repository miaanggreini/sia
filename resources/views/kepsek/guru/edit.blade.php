@extends('layouts.kepsek')

@section('content')
<div class="w-full px-6 lg:px-10">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-semibold">Edit Guru</h1>

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
        <form method="POST"
              action="{{ route('kepala_sekolah.data.guru.update', $guru) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="border rounded-2xl p-4 bg-gray-50 h-full">
                        <label class="block text-lg font-semibold text-indigo-700 mb-4">
                            Foto Guru
                        </label>

                        <div class="flex justify-center mb-4">
                            <img src="{{ $guru->foto_url }}"
                                 alt="Foto {{ $guru->nama }}"
                                 class="w-64 h-80 object-cover rounded-xl border shadow-sm">
                        </div>

                        <input type="file"
                               name="foto"
                               class="block w-full text-sm text-gray-700
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-medium
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">

                        @error('foto')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror

                        <p class="text-xs text-gray-500 mt-3">
                            Format: JPG/PNG/WEBP. Maksimal ukuran 2 MB.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="space-y-8">
                        <div>
                            <h2 class="text-xl font-semibold text-indigo-700 mb-4">Data Pribadi</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nama')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select name="jk"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="L" {{ old('jk', $guru->jk) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jk', $guru->jk) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jk')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nip')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">NUPTK <span class="text-red-500">*</span></label>
                                    <input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nuptk')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $guru->tempat_lahir) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('tempat_lahir')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal_lahir"
                                           value="{{ old('tanggal_lahir', optional($guru->tanggal_lahir)->format('Y-m-d')) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('tanggal_lahir')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold text-indigo-700 mb-4">Kepegawaian & Kontak</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Kepegawaian <span class="text-red-500">*</span></label>
                                    <select name="status_kepegawaian"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Pilih Status Kepegawaian --</option>
                                        <option value="PNS" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'PNS' ? 'selected' : '' }}>PNS</option>
                                        <option value="PPPK" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                        <option value="Non-PNS" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'Non-PNS' ? 'selected' : '' }}>Non-PNS</option>
                                    </select>
                                    @error('status_kepegawaian')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">No. HP <span class="text-red-500">*</span></label>
                                    <input type="text" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('no_hp')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $guru->email) }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('email')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                                    <select name="status"
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="aktif" {{ old('status', $guru->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="nonaktif" {{ old('status', $guru->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    @error('status')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap <span class="text-red-500">*</span></label>
                                    <textarea name="alamat" rows="4"
                                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('alamat', $guru->alamat) }}</textarea>
                                    @error('alamat')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <a href="{{ route('kepala_sekolah.data.guru') }}"
                               class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection