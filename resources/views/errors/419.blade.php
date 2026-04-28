<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Session expired — {{ config('app.name', 'Likha') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 text-center">
                <p class="text-secondary small mb-2">419</p>
                <h1 class="h4 mb-3">Page expired</h1>
                <p class="text-muted mb-4">This form timed out for security. Refresh the page and try again.</p>
                <a href="{{ url()->previous() ?: url('/') }}" class="btn btn-dark">Go back</a>
            </div>
        </div>
    </div>
</body>
</html>
