@extends('backend.layouts.app')
@section('title', __('Language List'))
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Language List</h4>
                                <div>
                                    <a href="{{ route('admin.language.create') }}" class="btn btn-primary">Add Language</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="language-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
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
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input language-status-toggle" id="status_{{ $language->id }}"
                                                                data-language-id="{{ $language->id }}" {{ $language->status ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status_{{ $language->id }}"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.language.edit', $language->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No data found</td>
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
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            initializeLanguageTable();
            initializeStatusToggle();
        });

        function initializeLanguageTable() {
            const table = $('#language-table');

            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
            }

            table.DataTable({
                pageLength: 100,
                lengthMenu: [10, 25, 50, 100, 200],
                drawCallback: function() {
                    restoreToggleStates();
                }
            });

            // Store initial toggle states
            storeToggleStates();
        }

        function initializeStatusToggle() {
            $(document).on('change', '.language-status-toggle', handleStatusToggle);
        }

        function handleStatusToggle() {
            const checkbox = $(this);

            if (checkbox.data('processing')) return;

            const languageId = checkbox.data('language-id');
            const status = checkbox.prop('checked') ? 1 : 0;

            checkbox.data('processing', true);

            updateLanguageStatus(checkbox, languageId, status);
        }

        function updateLanguageStatus(checkbox, languageId, status) {
            $.ajax({
                url: "{{ route('admin.language.update.status') }}",
                type: 'POST',
                data: {
                    id: languageId,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    handleStatusUpdateSuccess(checkbox, status, response);
                },
                error: function(xhr) {
                    handleStatusUpdateError(checkbox, xhr);
                },
                complete: function() {
                    checkbox.data('processing', false);
                }
            });
        }

        function handleStatusUpdateSuccess(checkbox, status, response) {
            if (response.status === 'success') {
                showToast('success', response.message);
                checkbox.data('initial-state', status);
            } else {
                checkbox.prop('checked', !status);
                showToast('error', response.message || 'Failed to update status');
            }
        }

        function handleStatusUpdateError(checkbox, xhr) {
            checkbox.prop('checked', !checkbox.prop('checked'));
            const errorMessage = xhr.responseJSON?.message || 'Failed to update language status';
            showToast('error', errorMessage);
        }

        function storeToggleStates() {
            $('.language-status-toggle').each(function() {
                $(this).data('initial-state', $(this).prop('checked'));
            });
        }

        function restoreToggleStates() {
            $('.language-status-toggle').each(function() {
                $(this).prop('checked', $(this).data('initial-state'));
            });
        }
    </script>
@endpush
