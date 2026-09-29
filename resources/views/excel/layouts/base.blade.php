<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

    {{-- HEADER --}}
    @include('excel.layouts.partials.header', [
        'nombre_empresa' => $nombre_empresa ?? '',
        'logo_empresa' => $logo_empresa ?? '',
        'nombre_informe' => $nombre_informe ?? '',
        'filtros' => $filtros ?? null,
        'usuario' => $usuario ?? 'Sistema',
    ])

    {{-- BODY --}}
    @yield('contenido')

    {{-- FOOTER --}}
    @include('excel.layouts.partials.footer', [
        'fecha_generacion' => \Carbon\Carbon::now()->format('Y-m-d H:i'),
    ])

</body>
</html>