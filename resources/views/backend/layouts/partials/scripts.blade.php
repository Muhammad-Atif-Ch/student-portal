<!-- General JS Scripts -->
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<!-- JS Libraies -->
<script src="{{ asset('assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Page Specific JS File -->
<script src="{{ asset('assets/js/page/index.js') }}"></script>
<script src="{{ asset('assets/js/page/datatables.js') }}"></script>
<script>
    window.routes = {
        translation: {
            progress: "{{ route('admin.translations.progress') }}",
            start: "{{ route('admin.translations.start') }}",
            stop: "{{ route('admin.translations.stop') }}"
        },
        setting: {
            index: "{{ route('admin.setting.index') }}",
            update: "{{ route('admin.setting.update') }}"
        },
        tts: {
            start: "{{ route('admin.translations.tts.start') }}",
            progress: "{{ route('admin.translations.tts.progress') }}",
            stop: "{{ route('admin.translations.tts.stop') }}"
        }
    };
</script>
<!-- Template JS File -->
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<!-- Custom JS File -->
<script src="{{ asset('assets/js/custom.js') }}"></script>

<script src="{{ asset('assets/js/pages/dashboard/index.js') }}"></script>

<!-- In the head section -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        @if (Session::has('success'))
            Toast.fire({
                icon: "success",
                title: "{!! session('success') !!}"
            });
        @endif

        @if (Session::has('error'))
            Toast.fire({
                icon: "error",
                title: "{!! session('error') !!}"
            });
        @endif

        @if (Session::has('warning'))
            Toast.fire({
                icon: "warning",
                title: "{!! session('warning') !!}"
            });
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.fire({
                    icon: "error",
                    title: "{!! $error !!}"
                });
            @endforeach
        @endif
    });
</script>
