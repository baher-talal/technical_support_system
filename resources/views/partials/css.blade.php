{{-- Bootstrap RTL + الأيقونات + خط Cairo: ملفات محلية من public/vendor إن وُجدت (php artisan assets:install)، وإلا CDN كاحتياط --}}
@php
    $has = fn (string $p) => is_file(public_path($p));
    $cdn = fn (bool $local, string $path, string $remote) => $local ? asset($path) : $remote;
    $bsCss = 'vendor/bootstrap/css/bootstrap.rtl.min.css';
    $iconCss = 'vendor/bootstrap-icons/font/bootstrap-icons.css';
    $fontCss = 'vendor/cairo/cairo.css';
@endphp
<link href="{{ $cdn($has($bsCss), $bsCss, 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
@if($icons ?? false)
<link href="{{ $cdn($has($iconCss), $iconCss, 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css') }}" rel="stylesheet">
@endif
<link href="{{ $cdn($has($fontCss), $fontCss, 'https://fonts.googleapis.com/css2?family=Cairo:wght@' . ($weights ?? '400;700') . '&display=swap') }}" rel="stylesheet">
