@extends('backend.layouts.app')
@section('title', __('Glossary'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Glossary</h4>
                                <div>
                                    <a href="#" class="btn btn-primary me-2" type="button" data-toggle="modal" data-target="#importFile">Import Excel</a>
                                    <a href="{{ route('admin.translations.glossary.destroy.all') }}" class="btn btn-danger text-white">Delete All</a>
                                    <a href="{{ route('admin.translations.glossary.create') }}" class="btn btn-primary">Add Glossary Term</a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if (session('import_failures'))
                                    <div class="alert alert-warning">
                                        <strong>Import completed with errors:</strong>
                                        <ul class="mb-0">
                                            @foreach (session('import_failures') as $failure)
                                                <li>
                                                    Row {{ $failure['row'] }} ({{ $failure['attribute'] }}):
                                                    {{ implode(', ', $failure['errors']) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-questions">
                                        <thead>
                                            <tr>
                                                <th class="text-center col-1">#</th>
                                                <th>Source Term</th>
                                                <th>Language</th>
                                                <th>Target Term</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($translationGlossary as $data)
                                                <tr>
                                                    <td class="text-center">{{ $data->id }}</td>
                                                    <td>{{ $data->source_term }}</td>
                                                    <td>{{ $data->language->name }}</td>
                                                    <td>{{ $data->target_term }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.translations.glossary.edit', $data->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.translations.glossary.destroy', $data->id) }}" method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $translationGlossary->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')

        <!-- Modal -->
        <div class="modal fade" id="importFile" tabindex="-1" role="dialog" aria-labelledby="importFileLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importFileLabel">{{ __('Import File') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.translations.glossary.import.file') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label>Language <small style="color: red">*</small></label>
                                        <select class="form-control" name="language_id" required>
                                            <option value="" selected>Select Option</option>
                                            @foreach ($languages as $language)
                                                <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label>{{ __('Import File') }}</label>
                                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Import File') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            // initSimpleDataTable('#table-questions', {
            //     pageLength: 100,
            //     lengthMenu: [10, 25, 50, 100, 200],
            //     language: {
            //         emptyTable: "No data found"
            //     }
            // });
            // if ($.fn.DataTable.isDataTable('#table-questions')) {
            //     $('#table-questions').DataTable().destroy();
            // }
            // $('#table-questions').DataTable({
            //     paging: false,
            //     ordering: true,
            //     searching: true
            // });
        });
    </script>
@endpush
