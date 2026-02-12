<!doctype html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8" />
<meta property="csp-nonce" content="{{ csp_nonce() }}">
<title inertia>{{ config('app.name', 'Laravel') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
<link rel="preconnect" href="{{ config('filesystems.disks.s3.url') }}" crossorigin="anonymous">
<link rel="preconnect" href="https://api.iconify.design" crossorigin="anonymous">
@vite('resources/js/app.ts')
@inertiaHead
@googlefonts
@googlefonts('serif')
@googlefonts('code')
</head>

<body class="antialiased">

    <div class="isolate">
        @inertia
    </div>

</body>
</html>
