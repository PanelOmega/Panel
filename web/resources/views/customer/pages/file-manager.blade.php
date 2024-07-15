<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico"/>
    <title>
        PanelOne - File Manager
    </title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script type="module" crossorigin src="{{asset('file-manager/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset('file-manager/app.css')}}">
</head>
<body>
<noscript>
    <strong>
        We're sorry but file-manager doesn't work properly without JavaScript enabled. Please enable it to continue.
    </strong>
</noscript>

<div class="container">
    <div id="fm" style="height: 700px"></div>
</div>

</body>
</html>
