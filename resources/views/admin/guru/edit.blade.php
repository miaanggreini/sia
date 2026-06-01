@extends('layouts.admin')

@section('content')
<div class="w-full">
  <div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-semibold">Edit Guru</h1>

    <a href="{{ route('admin.guru.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-indigo-600
              text-indigo-700 font-medium bg-white hover:bg-indigo-50
              focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
        <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
      </svg>
      Kembali
    </a>
  </div>

  <form method="POST"
        action="{{ route('admin.guru.update', $guru) }}"
        enctype="multipart/form-data">
    @method('PUT')
    @include('admin.guru._form', ['guru' => $guru])
  </form>
</div>
@endsection