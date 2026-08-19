@extends('backend.layouts.app')
@section('title', __('Questions List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>{{ $quiz->id }} - {{ $quiz->title }}</h4>
                                <div>
                                    {{-- <a href="{{ route('admin.quiz.question.sample.download') }}" class="btn btn-success me-2"><i class="fas fa-file-excel"></i> Download Sample</a> --}}
                                    <a href="#" class="btn btn-primary me-2" type="button" data-toggle="modal" data-target="#importFile"><i class="fas fa-file-excel"></i> Import</a>
                                    {{-- <a href="{{ route('admin.quiz.question.export', $quiz_id) }}" class="btn btn-primary"><i class="fas fa-file-excel"></i> Export</a> --}}
                                    <a href="{{ route('admin.quiz.question.destroy.all', $quiz_id) }}" class="btn btn-danger text-white">Delete All</a>
                                    <a href="{{ route('admin.quiz.question.create', $quiz_id) }}" class="btn btn-primary">Add Question</a>
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
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>A - Option</th>
                                                <th>B - Option</th>
                                                <th>C - Option</th>
                                                {{-- <th>Answer</th> --}}
                                                <th>Type</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($questions as $question)
                                                <tr>
                                                    <td>{{ $question->id }}</td>
                                                    {{-- <td>{{ $question->question }}</td> --}}
                                                    <td title="{{ $question->question }}">{{ Str::limit($question->question, 60) }}</td>
                                                    <td>{{ Str::limit($question->a, 30) }}</td>
                                                    <td>{{ Str::limit($question->b, 30) }}</td>
                                                    <td>{{ Str::limit($question->c, 30) }}</td>
                                                    {{-- <td>{{ $question->correct_answer }}</td> --}}
                                                    {{-- @dd($question->type, $question->type->pluck('type')->toArray()) --}}
                                                    {{-- <td>{{ $question->type->pluck('type')->implode(', ') }}</td> --}}
                                                    <td>
                                                        @foreach ($question->type as $type)
                                                            <span class="badge badge-primary mt-1">{{ $type->type }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.quiz.question.edit', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.quiz.question.destroy', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST"
                                                            class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $questions->links('pagination::bootstrap-5') }}
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
                        <h5 class="modal-title" id="importFileLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.quiz.question.import.file', $quiz_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 col-md-12 col-lg-12">
                                    <div class="form-group">
                                        <label>Import File</label>
                                        <input type="file" name="file" class="form-control"
                                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
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
