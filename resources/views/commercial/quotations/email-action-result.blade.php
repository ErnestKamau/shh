<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px 16px; }
        .card { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08); padding: 32px; }
        h1 { font-size: 1.35rem; margin: 0 0 12px; }
        p { line-height: 1.6; margin: 0; color: #334155; }
        .badge { display: inline-block; margin-bottom: 16px; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="card">
        @php
            $badgeClass = match ($variant ?? 'info') {
                'success' => 'badge-success',
                'warning' => 'badge-warning',
                'danger' => 'badge-danger',
                default => 'badge-info',
            };
        @endphp
        <span class="badge {{ $badgeClass }}">{{ ucfirst($variant ?? 'info') }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
