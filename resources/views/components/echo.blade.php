@php
    $echoConfig = config('reverb.apps.0') ?? [];
@endphp

<script nonce="{{ app('csp-nonce') }}">
    window.LaravelEchoConfig = {
        key: "{{ $echoConfig['key'] ?? 'app-key' }}",
        host: "{{ $echoConfig['host'] ?? 'localhost' }}",
        port: {{ (int) ($echoConfig['port'] ?? 6001) }},
        scheme: "{{ $echoConfig['scheme'] ?? 'http' }}"
    };
</script>
