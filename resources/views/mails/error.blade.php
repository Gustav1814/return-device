<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application Error</title>
</head>
<body>
    <h1>An error occurred in the application</h1>
    <p>{{ $exception->getMessage() }}</p>
    <pre>{{ $exception->getTraceAsString() }}</pre>
</body>
</html>
