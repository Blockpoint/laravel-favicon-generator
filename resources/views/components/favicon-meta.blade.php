{{-- Favicon Meta Tags Component --}}
<link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
<link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
<link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

{{-- Web App Title Meta Tags --}}
@php
    $manifestPath = public_path('favicon/site.webmanifest');
    $appName = '';
    $shortName = '';

    if (file_exists($manifestPath)) {
        $manifestContent = json_decode(file_get_contents($manifestPath), true);
        $appName = $manifestContent['name'] ?? config('app.name', '');
        $shortName = $manifestContent['short_name'] ?? $appName;
    } else {
        $appName = config('app.name', '');
        $shortName = $appName;
    }
@endphp

@if(!empty($appName))
<meta name="application-name" content="{{ $appName }}" />
<meta name="apple-mobile-web-app-title" content="{{ $appName }}" />
@endif
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
