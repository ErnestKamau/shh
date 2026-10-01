@php
    $companyName = trim((string) (getActiveCompany()?->name ?? '')) ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — {{ $companyName }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f5f3f2;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f1f1f;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-top: 4px solid #8B1A1A;
            border-radius: 8px;
            padding: 28px 24px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .company {
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #8B1A1A;
            font-weight: 600;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 22px;
            margin: 0 0 10px;
        }
        p {
            margin: 0;
            font-size: 15px;
            line-height: 1.5;
            color: #555;
        }
        .links {
            list-style: none;
            margin: 20px 0 0;
            padding: 0;
        }
        .links li + li { margin-top: 10px; }
        .links a {
            display: block;
            padding: 12px 14px;
            border: 1px solid #e3d6d6;
            border-radius: 6px;
            color: #8B1A1A;
            font-weight: 600;
            text-decoration: none;
        }
        .links a:hover, .links a:focus { background: #fbf6f6; }
    </style>
</head>
<body>
    <main class="card">
        <div class="company">{{ $companyName }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        @if(!empty($links))
            <ul class="links">
                @foreach($links as $link)
                    <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                @endforeach
            </ul>
        @endif
    </main>
</body>
</html>
