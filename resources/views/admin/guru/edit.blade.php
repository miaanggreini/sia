  @extends('layouts.admin')

  @section('content')
  <div class="w-full">
    <h1 class="text-2xl font-semibold mb-4">Edit Guru</h1>

    <div>
      <form method="POST"
            action="{{ route('admin.guru.update', $guru) }}"
            enctype="multipart/form-data">
        @method('PUT')
        @include('admin.guru._form', ['guru' => $guru])
      </form>
    </div>
  </div>
  @endsection