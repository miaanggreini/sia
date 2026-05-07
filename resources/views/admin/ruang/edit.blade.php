@extends('layouts.admin')
@section('title','Edit Ruang')

@section('content')
  <h1 class="text-2xl font-semibold mb-4">Edit Ruang</h1>
  <div class="bg-white border rounded-md p-4">
    <form method="POST" action="{{ route('admin.ruang-kelas.update',$item) }}">
      @method('PUT')
      @include('admin.ruang._form')
    </form>
  </div>
@endsection
