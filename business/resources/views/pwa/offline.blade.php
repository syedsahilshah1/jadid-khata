<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'UltimatePOS') }} - Offline</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Open Sans', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f4f7;
            color: #111827;
            text-align: center;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }
        p {
            margin-bottom: 1.5rem;
            color: #4b5563;
            line-height: 1.6;
        }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        a:hover {
            background: #1d4ed8;
        }
        small {
            display: block;
            margin-top: 1.5rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Connecting to {{ config('app.name', 'Jadid Khata') }}</h1>
        <p>Starting local services. Please wait...</p>
        <a href="{{ url('/login') }}" id="retryBtn">Retry</a>
        <small>{{ config('app.name', 'Jadid Khata') }}</small>
    </div>
    <script>
        // Auto-check local server and redirect to /login immediately when ready
        function checkServer() {
            fetch('/login', { method: 'HEAD', cache: 'no-store' })
                .then(function(res) {
                    if (res.ok || res.status < 500) {
                        window.location.href = '/login';
                    }
                })
                .catch(function() {});
        }
        setInterval(checkServer, 1500);
        checkServer();
    </script>
</body>
</html>
