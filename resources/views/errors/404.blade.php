<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Page not found — {{ config('app.name', 'Likha') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 text-center">
                <p class="text-secondary small mb-2">404</p>
                <h1 class="h4 mb-3">Page not found</h1>
                <p class="text-muted mb-4">The page you’re looking for isn’t here. It may have moved or the link may be wrong.</p>
                <a href="{{ url('/') }}" class="btn btn-dark">Back to home</a>
            </div>
        </div>
    </div>
</body>
</html>
