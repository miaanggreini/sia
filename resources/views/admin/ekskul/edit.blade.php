{{-- resources/views/admin/ekskul/edit.blade.php --}}
@extends('layouts.admin')
@section('title','Ubah Ekskul')

@section('content')
  <h1 class="text-2xl font-semibold mb-4">Ubah Ekskul</h1>

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
    <form action="{{ route('admin.ekskul.update', $ekskul) }}" method="POST">
      @csrf
      @method('PUT')

      @include('admin.ekskul._form')
    </form>
  </div>
@endsection
