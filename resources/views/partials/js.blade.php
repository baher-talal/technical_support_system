@php $bsJs = 'vendor/bootstrap/js/bootstrap.bundle.min.js'; @endphp
<script src="{{ is_file(public_path($bsJs)) ? asset($bsJs) : 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js' }}"></script>
