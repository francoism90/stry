<!doctype html>
<html class="scroll-smooth" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="referrer" content="strict-origin-when-cross-origin">
<link rel="preconnect" href="{{ config('filesystems.disks.s3.url') }}" crossorigin="anonymous">
@pwaHead
@fonts
@vite('resources/js/app.ts')
<x-inertia::head />
</head>

<body class="antialiased">

    <div class="isolate">
        <x-inertia::app />
    </div>

    @pwaSw

</body>
</html>
