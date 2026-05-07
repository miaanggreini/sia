@props([
  'title' => '',
  'value' => '',
  'hint'  => null,
  // variant: default|success|warning|info|danger
  'variant' => 'default',
])

@php
$palette = [
  'default' => ['bg' => 'bg-white',     'ring' => 'border-gray-200',  'text' => 'text-gray-700',  'accent' => 'text-gray-500'],
  'success' => ['bg' => 'bg-emerald-50','ring' => 'border-emerald-200','text' => 'text-emerald-900','accent' => 'text-emerald-700'],
  'warning' => ['bg' => 'bg-amber-50',  'ring' => 'border-amber-200', 'text' => 'text-amber-900', 'accent' => 'text-amber-700'],
  'info'    => ['bg' => 'bg-sky-50',    'ring' => 'border-sky-200',   'text' => 'text-sky-900',   'accent' => 'text-sky-700'],
  'danger'  => ['bg' => 'bg-rose-50',   'ring' => 'border-rose-200',  'text' => 'text-rose-900',  'accent' => 'text-rose-700'],
];
$c = $palette[$variant] ?? $palette['default'];
@endphp

<div {{ $attributes->class("rounded-xl border {$c['ring']} {$c['bg']} p-4") }}>
  @if($title)
    <div class="text-xs {{ $c['accent'] }} mb-1">{{ $title }}</div>
  @endif
  <div class="text-2xl font-semibold {{ $c['text'] }}">{{ $value }}</div>
  @if($hint)
    <div class="text-xs mt-1 {{ $c['accent'] }}">{{ $hint }}</div>
  @endif
</div>
