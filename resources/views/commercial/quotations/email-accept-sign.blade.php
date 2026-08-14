<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept quotation {{ $quotation->quote_number }}</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 40px 16px; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08); padding: 32px; }
        h1 { font-size: 1.35rem; margin: 0 0 8px; }
        .muted { color: #64748b; margin-bottom: 24px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; }
        input[type=text] { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 16px; box-sizing: border-box; }
        canvas { width: 100%; height: 160px; border: 1px solid #cbd5e1; border-radius: 8px; touch-action: none; }
        .actions { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
        button, .btn { border: 0; border-radius: 8px; padding: 10px 18px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #16a34a; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Accept quotation {{ $quotation->quote_number }}</h1>
        <p class="muted">Please sign below to accept this quotation on behalf of {{ $enquiry->customer?->name ?? 'your organization' }}.</p>

        <form method="POST" action="{{ route('commercial.quotations.email-accept.submit', ['enquiry' => $enquiry->id, 'quotation' => $quotation->id, 'contact' => $contact->id, 'token' => $token]) }}" id="accept-form">
            @csrf
            <label for="signer_name">Your name</label>
            <input type="text" id="signer_name" name="signer_name" value="{{ old('signer_name', $signerName) }}" required>

            <label>Signature</label>
            <canvas id="signature-canvas"></canvas>
            <input type="hidden" name="signature" id="signature-input">

            <div class="actions">
                <button type="button" class="btn-secondary" id="clear-signature">Clear</button>
                <button type="submit" class="btn-primary">Accept quotation</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const canvas = document.getElementById('signature-canvas');
            const ctx = canvas.getContext('2d');
            const input = document.getElementById('signature-input');
            const form = document.getElementById('accept-form');
            let drawing = false;

            function resize() {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * window.devicePixelRatio;
                canvas.height = rect.height * window.devicePixelRatio;
                ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#0f172a';
            }

            function pos(event) {
                const rect = canvas.getBoundingClientRect();
                const source = event.touches ? event.touches[0] : event;
                return { x: source.clientX - rect.left, y: source.clientY - rect.top };
            }

            function start(event) {
                drawing = true;
                const p = pos(event);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                event.preventDefault();
            }

            function move(event) {
                if (!drawing) return;
                const p = pos(event);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
                event.preventDefault();
            }

            function end() {
                drawing = false;
            }

            resize();
            window.addEventListener('resize', resize);
            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            canvas.addEventListener('touchend', end);

            document.getElementById('clear-signature').addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                input.value = '';
            });

            form.addEventListener('submit', function (event) {
                input.value = canvas.toDataURL('image/png');
                if (!input.value || input.value.length < 30) {
                    event.preventDefault();
                    alert('Please provide your signature.');
                }
            });
        })();
    </script>
</body>
</html>
