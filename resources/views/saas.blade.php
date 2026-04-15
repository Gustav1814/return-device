<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    @php
        $appRootPath = parse_url(url('/'), PHP_URL_PATH);
        $appRootPath = is_string($appRootPath) ? rtrim($appRootPath, '/') : '';
        $saasBasename = ($appRootPath === '' ? '' : $appRootPath) . '/saas';
    @endphp
    <meta name="saas-basename" content="{{ $saasBasename }}">

    <title>{{ config('app.name', 'DeviceReturn') }} — SaaS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/react.css', 'resources/js/react/main.tsx'])
  </head>
  <body>
    <div id="root"></div>
  </body>
</html>
