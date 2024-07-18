<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico"/>
    <title>
        PanelOne - File Manager
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script type="module" crossorigin src="{{asset('file-manager/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset('file-manager/app.css')}}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito-sans:400,500,600,700&display=swap" rel="stylesheet" />


    <style>
        body {
            font-family: 'Nunito Sans';
        }
    </style>

</head>
<body>
<noscript>
    <strong>
        We're sorry but file-manager doesn't work properly without JavaScript enabled. Please enable it to continue.
    </strong>
</noscript>

<div class="fm-wrapper">
    <div id="fm" style="height: 100vh"></div>
</div>

</body>
</html>
