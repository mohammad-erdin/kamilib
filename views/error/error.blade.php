<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{$status}}</title>
</head>
<body>
    <code>
        {{ $error->getMessage() }}<br>
        {{ $error->getFile() }} {{ $error->getLine() }}<br>
        {{ $error->getPrevious()}}<br>
        {{ $error->getTraceAsString()}}<br>
    </code>
</body>
</html>