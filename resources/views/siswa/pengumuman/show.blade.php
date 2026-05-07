@extends('layouts.siswa')

@section('content')
<style>
  .rt { line-height:1.30; }
  .rt p { margin: .2rem 0; }
  .rt ul, .rt ol { margin: .2rem 0 .5rem 1.25rem; }
  .rt li { margin: .15rem 0; }
  .rt h1, .rt h2, .rt h3, .rt h4, .rt h5, .rt h6 { margin: .5rem 0 .35rem; line-height:1.25; }
  .rt blockquote { margin: .5rem 0; padding-left:.75rem; border-left:3px solid #e5e7eb; color:#374151; }
  .rt br + br { line-height: .1rem; }
</style>

@php
  use Illuminate\Support\Str;

  $tanggalTampil = $p->published_at
      ?? $p->approved_at
      ?? $p->updated_at
      ?? $p->created_at;

  $raw = $p->isi ?? '';

  $hasHtml = Str::contains($raw, ['<p','<br','<ul','<ol','<h1','<h2','<h3','<div','<span']);

  if (!$hasHtml) {
      $raw = preg_replace("/(\r?\n){3,}/", "\n\n", $raw);
      $raw = preg_replace("/^[ \t]+/m", "", $raw);
  }
@endphp

<div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

  {{-- HEADER --}}
  <div class="flex items-center justify-between border-b bg-gray-50 px-4 py-4 sm:px-6">
    <a href="{{ route('siswa.pengumuman.index') }}"
       class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">
      &larr; Kembali
    </a>

    <div class="text-right">
      <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-600 border">
        {{ $tanggalTampil ? \Carbon\Carbon::parse($tanggalTampil)->format('d M Y') : '-' }}
      </span>
      <div class="mt-1 text-[11px] text-gray-400">
        Dipublikasikan
      </div>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="px-4 py-6 sm:px-6">
    <h1 class="mb-3 text-2xl font-bold text-gray-900">
      {{ $p->judul }}
    </h1>

    @if($hasHtml)
      <article class="rt text-[15px] text-gray-900 sm:text-base">
        {!! $raw !!}
      </article>
    @else
      <article class="rt whitespace-pre-line text-[15px] text-gray-900 sm:text-base">
        {!! nl2br(e($raw)) !!}
      </article>
    @endif
  </div>
</div>
@endsection