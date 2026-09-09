@once
    <link rel="stylesheet" href="{{ asset('css/dashboard-motion.css') }}?v={{ filemtime(public_path('css/dashboard-motion.css')) }}">
    <script src="{{ asset('js/dashboard-motion.js') }}?v={{ filemtime(public_path('js/dashboard-motion.js')) }}" defer></script>
@endonce
