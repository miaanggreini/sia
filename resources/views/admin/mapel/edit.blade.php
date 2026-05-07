@extends('layouts.admin')

@section('content')
  <h1 class="text-2xl font-semibold mb-4">Ubah Mata Pelajaran</h1>

  <div class="bg-white rounded-lg shadow p-4">
    <form method="POST" action="{{ route('admin.mapel.update', $mapel) }}">
      @method('PUT')
      @include('admin.mapel._form', ['mapel' => $mapel])
    </form>
  </div>
@endsection
