<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Laravel') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased min-h-screen bg-slate-50">
  <main class="min-h-screen flex items-center justify-center px-4 py-10">
    {{ $slot }}
  </main>
</body>
</html>
