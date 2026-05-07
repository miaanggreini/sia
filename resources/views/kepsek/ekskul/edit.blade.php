@extends('layouts.kepsek')
@section('title','Ubah Ekskul')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Ubah Ekskul</h1>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-xl shadow p-6">
    <form action="{{ route('kepala_sekolah.data.ekskul.update', $ekskul) }}" method="POST">
      @csrf
      @method('PUT')

      @include('admin.ekskul._form')
    </form>
  </div>
</div>
@endsection