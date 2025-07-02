<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- index.html  21 Nov 2019 03:44:50 GMT -->

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        @include('backend.layouts.partials.css')

        <style>
            .small-toast {
                font-size: 1rem !important;
                padding: 0.7rem !important;
                min-width: 280px !important;
                max-width: 350px !important;
            }

            .small-toast-title {
                font-size: 1rem !important;
                margin: 0 !important;
                padding-left: 12px !important;
                line-height: 1.5 !important;
            }

            .swal2-icon {
                margin: 0 !important;
                height: 2.5em !important;
                width: 2.5em !important;
                border: none !important;
            }

            .swal2-icon .swal2-icon-content {
                font-size: 1.5em !important;
            }

            .swal2-timer-progress-bar {
                height: 0.2rem !important;
            }
        </style>
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
            // Set up CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

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
            $(document).ready(function() {
                if ($.fn.DataTable.isDataTable('#table-1')) {
                    $('#table-1').DataTable().destroy(); // Destroy existing instance
                }

                $('#table-1').DataTable({
                    "pageLength": 100, // Show 100 rows by default
                    "lengthMenu": [10, 25, 50, 100, 200] // Allow users to change row count
                });
            });

            function showToast(icon, title) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'small-toast',
                        title: 'small-toast-title'
                    },
                    iconHtml: ''
                });

                Toast.fire({
                    icon: icon,
                    title: title,
                    padding: '0.7em',
                    width: 'auto'
                });
            }
        </script>

        @stack('scripts')
    </body>

    <!-- index.html  21 Nov 2019 03:47:04 GMT -->

</html>
