<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/livewire/livewire.js', 'GET');
$response = $kernel->handle($request);

echo 'status: '.$response->getStatusCode().PHP_EOL;
echo 'content-type: '.$response->headers->get('Content-Type', '').PHP_EOL;
if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
    $path = $response->getFile()->getPathname();
    echo 'file: '.$path.PHP_EOL;
    echo 'first-bytes: '.substr((string) file_get_contents($path), 0, 120).PHP_EOL;
} else {
    echo 'first-bytes: '.substr((string) $response->getContent(), 0, 120).PHP_EOL;
}

$kernel->terminate($request, $response);
