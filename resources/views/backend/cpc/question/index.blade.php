@extends('backend.layouts.app')
@section('title', __('CPC Question List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>CPC Questions</h4>
                                <div>
                                    <a href="{{ route('admin.cpc.question.sample.download') }}" class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Download Sample</a>
                                    <a href="#" class="btn btn-primary me-2" data-toggle="modal" data-target="#importFile"><i class="fas fa-file-excel"></i> Import</a>
                                    <a href="{{ route('admin.cpc.question.create') }}" class="btn btn-primary">Add CPC Question</a>
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
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Case Study</th>
                                                <th>Correct Option</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($cpcQuestions as $cpcQuestion)
                                                <tr>
                                                    <td>{{ $cpcQuestion->id }}</td>
                                                    <td>{{ Str::limit($cpcQuestion->question, 100) }}</td>
                                                    <td>{{ $cpcQuestion->caseStudy->title ?? '-' }}</td>
                                                    <td>
                                                        @php $correct = $cpcQuestion->options->firstWhere('is_correct', true); @endphp
                                                        @if ($correct)
                                                            <span class="badge badge-success">{{ strtoupper($correct->option_key) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.cpc.question.edit', $cpcQuestion->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.cpc.question.destroy', $cpcQuestion->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No CPC questions found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $cpcQuestions->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')

        <!-- Import Modal -->
        <div class="modal fade" id="importFile" tabindex="-1" role="dialog" aria-labelledby="importFileLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importFileLabel">Import CPC Questions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.cpc.question.import.file') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Import File</label>
                                        <input type="file" name="file" class="form-control"
                                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                        <small class="form-text text-muted">
                                            Download the sample file to see the required format.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
