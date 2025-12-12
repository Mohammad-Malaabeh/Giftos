<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('subject', config('app.name'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: #f6f9fc;
            color: #334155;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Inter, Arial, sans-serif;
            margin: 0;
            padding: 24px;
        }

        .wrapper {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e5e7eb;
        }

        .btn {
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            display: inline-block;
            padding: 10px 16px;
            border-radius: 8px;
        }

        .muted {
            color: #6b7280;
            font-size: 12px;
        }

        h1,
        h2,
        h3 {
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        @yield('content')
        <p class="muted" style="margin-top:24px;">
            {{ config('app.name') }} • {{ config('app.url') }}
        </p>
    </div>
</body>

</html>
