{{-- resources/views/admin/siswa/edit.blade.php --}}
@extends('layouts.admin')
@section('title','Ubah Siswa')

@section('content')
  <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Ubah Siswa</h1>
      <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-indigo-600
              text-indigo-700 font-medium bg-white hover:bg-indigo-50
              focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
        </svg>
        Kembali
      </a>
  </div>

  @if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
      <div class="font-semibold text-lg">Data belum valid.</div>
      <p class="mt-1 text-sm">Periksa kembali kolom yang ditandai merah, lalu coba simpan lagi.</p>
      <ul class="mt-3 list-disc list-inside text-sm space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.siswa.update', $siswa) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
      @csrf
      @method('PUT')
      @include('admin.siswa._form', ['mode' => 'form', 'siswa' => $siswa])
  </form>
@endsection