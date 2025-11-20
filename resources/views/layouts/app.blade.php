<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Conquete Spatiale')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap');

        body {
            font-family: 'Share Tech Mono', monospace;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3a 50%, #0a0a2a 100%);
        }

        .font-orbitron {
            font-family: 'Orbitron', sans-serif;
        }

        .glow-text {
            text-shadow: 0 0 10px currentColor, 0 0 20px currentColor;
        }

        .stars {
            background-image:
                radial-gradient(2px 2px at 20px 30px, #eee, transparent),
                radial-gradient(2px 2px at 40px 70px, #fff, transparent),
                radial-gradient(1px 1px at 90px 40px, #fff, transparent),
                radial-gradient(2px 2px at 160px 120px, #ddd, transparent),
                radial-gradient(1px 1px at 230px 80px, #fff, transparent);
            background-size: 250px 200px;
            animation: twinkle 5s ease-in-out infinite alternate;
        }

        @keyframes twinkle {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .console-text {
            font-family: 'Share Tech Mono', monospace;
        }

        /* Scrollbar style */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1a1a3a;
        }
        ::-webkit-scrollbar-thumb {
            background: #4a9eff;
            border-radius: 4px;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen text-gray-100 stars">
    @yield('content')

    @stack('scripts')
</body>
</html>
