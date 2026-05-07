@extends('layouts.kepsek')
@section('title', 'Edit Ruang')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Edit Ruang</h1>
  </div>

  <div class="bg-white border rounded-2xl p-6 shadow-sm">
    <form method="POST" action="{{ route('kepala_sekolah.data.ruang-kelas.update', $item) }}">
      @csrf
      @method('PUT')
      @include('admin.ruang._form')
    </form>
  </div>
</div>
@endsection