<!DOCTYPE html>
<html lang="en">

    <!-- index.html  21 Nov 2019 03:44:50 GMT -->

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
        <title>@yield('title')</title>
        @include('backend.layouts.partials.css')
    </head>

    <body>
        <div class="loader"></div>
        <div id="app">
            <div class="main-wrapper main-wrapper-1">
                @include('backend.layouts.partials.header')
                @include('backend.layouts.partials.sidebar')
                @yield('content')
                @include('backend.layouts.partials.footer')
            </div>
        </div>
        @include('backend.layouts.partials.scripts')
    </body>

    <!-- index.html  21 Nov 2019 03:47:04 GMT -->

</html>
