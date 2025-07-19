<!doctype html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8" />
<title inertia>{{ config('app.name', 'Laravel') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="color-scheme" content="dark">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/images/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/images/favicon-16x16.png') }}">
<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="{{ asset('storage/images/apple-touch-icon.png') }}">
@vite('resources/js/app.ts')
@inertiaHead
@googlefonts
@googlefonts('serif')
@googlefonts('code')
</head>

<body class="antialiased">

@inertia

</body>
</html>
