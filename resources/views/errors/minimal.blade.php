<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} | Next Level School</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: "Figtree", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f2540;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 18px;
            padding: 48px 32px;
            text-align: center;
            box-shadow: 0 20px 45px -20px rgba(15, 37, 64, .35);
        }
        .code {
            font-size: 72px;
            font-weight: 800;
            line-height: 1;
            color: #2474bd;
            letter-spacing: -2px;
        }
        h1 { font-size: 22px; margin: 20px 0 10px; }
        p { margin: 0 0 28px; color: #475569; line-height: 1.6; }
        a {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 10px;
            background: #0f2540;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
        }
        a:hover { background: #1b3a5e; }
    </style>
</head>
<body>
    <main class="card">
        <div class="code">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a href="{{ url('/') }}">Volver al inicio</a>
    </main>
</body>
</html>
