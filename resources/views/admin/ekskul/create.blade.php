{{-- resources/views/admin/ekskul/create.blade.php --}}
@extends('layouts.admin')
@section('title','Tambah Ekskul')

@section('content')
  <h1 class="text-2xl font-semibold mb-4">Tambah Ekskul</h1>

  <div class="bg-white rounded-xl shadow p-6">
    <form action="{{ route('admin.ekskul.store') }}" method="POST">
      @csrf

      @include('admin.ekskul._form')
    </form>
  </div>
@endsection
