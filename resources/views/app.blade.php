<!doctype html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8" />
<title inertia>{{ config('app.name', 'Laravel') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="{{ config('filesystems.disks.s3.url') }}" crossorigin="anonymous">
@googlefonts(['nonce' => app('csp-nonce')])
@googlefonts(['font' => 'code', 'nonce' => app('csp-nonce')])
@vite('resources/js/app.ts')
@inertiaHead
@pwaHead
</head>

<body class="antialiased">

    <div class="isolate">
        @inertia
    </div>

    @pwaSw

</body>
</html>
