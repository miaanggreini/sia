@extends('layouts.kepsek')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<div class="w-full">

  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-gray-900">
      Edit Mata Pelajaran
    </h1>
  </div>

  <div class="rounded-lg bg-white p-6 shadow">
    @include('admin.mapel._form', [
      'mapel' => $mapel,
      'formAction' => route('kepala_sekolah.data.mapel.update', $mapel),
      'formMethod' => 'PUT',
      'backUrl' => route('kepala_sekolah.data.mapel'),
    ])
  </div>
</div>
@endsection