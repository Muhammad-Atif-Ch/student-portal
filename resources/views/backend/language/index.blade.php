@extends('backend.layouts.app')
@section('title', __('Language List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Language List</h4>
                                {{-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add User</a> --}}
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Family</th>
                                                <th>Name</th>
                                                <th>Native Name</th>
                                                <th>Code</th>
                                                <th>Code 2</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($languages as $language)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $language->family }}</td>
                                                    <td>{{ $language->name }}</td>
                                                    <td>{{ $language->native_name }}</td>
                                                    <td>{{ $language->code ?? 'N/A' }}</td>
                                                    <td>{{ $language->code_2 ?? 'N/A' }}</td>
                                                    <td>
                                                        <label class="custom-switch mt-2" style="cursor: pointer;">
                                                            <input type="checkbox" name="status" class="custom-switch-input language-status-toggle" data-language-id="{{ $language->id }}"
                                                                {{ $language->status ? 'checked' : '' }}>
                                                            <span class="custom-switch-indicator"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.language.edit', $language->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="8"> No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection
@push('scripts')
    <script>
        // Initialize the table and event handlers only once
        (function() {
            // Check if initialization has already occurred
            if (window.languageTableInitialized) {
                return;
            }

            $(function() {
                // Initialize DataTable
                if ($.fn.DataTable.isDataTable('#table-1')) {
                    $('#table-1').DataTable().destroy();
                }

                $('#table-1').DataTable({
                    "pageLength": 100,
                    "lengthMenu": [10, 25, 50, 100, 200],
                    "drawCallback": function() {
                        // Ensure switches maintain their state after redraw
                        $('.language-status-toggle').each(function() {
                            $(this).prop('checked', $(this).data('initial-state'));
                        });
                    }
                });

                // Store initial states of checkboxes
                $('.language-status-toggle').each(function() {
                    $(this).data('initial-state', $(this).prop('checked'));
                });

                // Use event delegation for the status toggle
                $(document).on('change', '.language-status-toggle', function() {
                    const checkbox = $(this);

                    // Prevent multiple simultaneous requests
                    if (checkbox.data('processing')) {
                        return;
                    }

                    const languageId = checkbox.data('language-id');
                    const status = checkbox.prop('checked') ? 1 : 0;

                    // Set processing flag
                    checkbox.data('processing', true);

                    $.ajax({
                        url: "{{ route('admin.language.update.status') }}",
                        type: 'POST',
                        data: {
                            id: languageId,
                            status: status,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                showToast('success', response.message);
                                // Update the stored state
                                checkbox.data('initial-state', status);
                            } else {
                                checkbox.prop('checked', !status);
                                showToast('error', response.message || 'Failed to update status');
                            }
                        },
                        error: function(xhr) {
                            checkbox.prop('checked', !status);
                            const errorMessage = xhr.responseJSON?.message || 'Failed to update language status';
                            showToast('error', errorMessage);
                        },
                        complete: function() {
                            // Clear processing flag
                            checkbox.data('processing', false);
                        }
                    });
                });
            });

            // Mark as initialized
            window.languageTableInitialized = true;
        })();
    </script>
@endpush
