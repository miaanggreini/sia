@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')

@section('content')
  {{-- Header --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Tambah Pengumuman</h1>
      <p class="text-gray-600 mt-1">Lengkapi data pengumuman dengan benar.</p>
    </div>

    <a href="{{ route('admin.pengumuman.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border-2 border-indigo-500 text-indigo-600 hover:bg-indigo-50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
      </svg>
      Kembali
    </a>
  </div>

  {{-- Notifikasi Error --}}
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

  {{-- Card Form --}}
  <div class="bg-white rounded-2xl shadow p-6">
    <form method="POST" action="{{ route('admin.pengumuman.store') }}" class="space-y-6">
      @csrf

      @include('admin.pengumuman._form', ['item' => $item ?? new \App\Models\Pengumuman()])

    </form>
  </div>
@endsection
