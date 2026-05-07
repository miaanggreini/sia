@extends('layouts.kepsek')

@section('title', 'Detail Siswa')

@section('content')
<div class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-gray-900">
            Detail Siswa
        </h1>

        <a href="{{ route('kepala_sekolah.data.siswa') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-indigo-600 bg-white px-4 py-2 font-medium text-indigo-700 hover:bg-indigo-50">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 viewBox="0 0 24 24"
                 fill="currentColor">
                <path d="M10.828 11H20a1 1 0 110 2h-9.172l3.536 3.536a1 1 0 11-1.414 1.414l-5.243-5.243a1 1 0 010-1.414l5.243-5.243a1 1 0 111.414 1.414L10.828 11z"/>
            </svg>
            Kembali
        </a>
    </div>

    @include('admin.siswa._form', [
        'mode' => 'show',
        'siswa' => $siswa,
        'backUrl' => route('kepala_sekolah.data.siswa'),
    ])
</div>
@endsection