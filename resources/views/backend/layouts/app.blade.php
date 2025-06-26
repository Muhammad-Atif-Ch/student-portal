<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- index.html  21 Nov 2019 03:44:50 GMT -->

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
        <title>@yield('title')</title>
        @include('backend.layouts.partials.css')
    </head>

    <body class="@stack('body-class')">
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
        <!-- JS Libraries -->
        <script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
        <!-- SweetAlert2 from CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Custom JS Files -->
        <script src="{{ asset('assets/js/broadcast.js') }}"></script>
        <script src="{{ asset('assets/js/custom.js') }}"></script>
        <!-- Make Laravel routes available to JavaScript -->
        <script>
            var routes = {
                translation: {
                    progress: "{{ route('admin.translations.progress') }}",
                    start: "{{ route('admin.translations.start') }}",
                    stop: "{{ route('admin.translations.stop') }}"
                },
                setting: {
                    index: "{{ route('admin.setting.index') }}",
                    update: "{{ route('admin.setting.update') }}",
                    resetDefault: "{{ route('admin.setting.resetDefault') }}"
                },
                tts: {
                    start: "{{ route('admin.translations.tts.start') }}",
                    progress: "{{ route('admin.translations.tts.progress') }}",
                    stop: "{{ route('admin.translations.tts.stop') }}"
                }
            };
        </script>
    </body>

    <!-- index.html  21 Nov 2019 03:47:04 GMT -->

</html>
