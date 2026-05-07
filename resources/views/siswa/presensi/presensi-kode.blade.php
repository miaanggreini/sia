@extends('layouts.siswa')
@section('title','Presensi • Masukkan Kode')

@section('content')
<div class="max-w-md mx-auto">

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Masukkan Kode Presensi</h1>
    <p class="mt-1 text-sm text-gray-600">
      Masukkan kode dari guru untuk melakukan presensi.
    </p>
  </div>

  <div class="bg-white border rounded-2xl shadow-sm p-6">
    <form method="POST" action="{{ route('siswa.presensi.kode.pakai') }}" class="space-y-4">
      @csrf

      <div>
        <label class="text-sm font-medium text-gray-800">Kode</label>
        <input name="kode"
               maxlength="8"
               required
               autofocus
               class="mt-2 w-full border rounded-xl px-4 py-3 text-center font-mono tracking-widest text-lg
                      focus:ring-2 focus:ring-blue-200 focus:border-blue-300"
               placeholder="ABC123">
        <p class="mt-2 text-xs text-gray-500">
          Kode berlaku terbatas (~20 menit) dan khusus sesi rombel ini.
        </p>

        @error('kode')
          <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
        @enderror

        @if(session('error'))
          <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2 text-sm">
            {{ session('error') }}
          </div>
        @endif
      </div>

      <button class="w-full px-4 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">
        Presensi
      </button>

      <a href="{{ route('siswa.presensi.index') }}"
         class="block text-center text-sm text-gray-600 hover:text-gray-800">
        Kembali ke Presensi
      </a>
    </form>
  </div>
</div>
@endsection