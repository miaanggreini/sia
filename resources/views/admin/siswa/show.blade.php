{{-- resources/views/admin/siswa/show.blade.php --}}
@extends('layouts.admin')
@section('title','Detail Siswa')

@section('content')
  <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Detail Siswa</h1>
  </div>

  {{-- Tidak perlu <form> karena read-only --}}
  @include('admin.siswa._form', ['mode' => 'show', 'siswa' => $siswa])
@endsection
