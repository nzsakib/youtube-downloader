<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Youtube Downloader</title>
    
    <link rel="stylesheet" href="/css/app.css">

</head>
<body>

    <div id="app">
        @yield('content')
    </div>


    <script src="js/app.js"></script>
    @yield('scripts')
    @yield('extraJS')
</body>
</html>