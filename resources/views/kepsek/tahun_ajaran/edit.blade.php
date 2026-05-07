@extends('layouts.kepsek')
@section('title','Ubah Tahun Ajaran')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-semibold">Ubah Tahun Ajaran</h1>
      <p class="text-sm text-gray-500 mt-1">
        Semester aktif dapat diubah dari Ganjil ke Genap tanpa membuat tahun ajaran baru.
      </p>
    </div>

  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 p-3">
      <div class="font-medium mb-1">Periksa kembali isian berikut:</div>
      <ul class="list-disc ml-5 text-sm">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-2xl shadow p-6">
    <form method="POST" action="{{ route('kepala_sekolah.data.tahun-ajaran.update', $item) }}" class="space-y-6">
      @csrf
      @method('PUT')
      @include('admin.tahun_ajaran._form', ['item' => $item])

      <div class="pt-4 border-t flex items-center gap-3">
        <a href="{{ route('kepala_sekolah.data.tahun-ajaran') }}"
           class="px-4 py-2 rounded-md border hover:bg-gray-50">
          Batal
        </a>
        <button class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection