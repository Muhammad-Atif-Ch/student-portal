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
                                <h4>{{ $quiz->title }}</h4>
                                <div>
                                    <a href="#" class="btn btn-primary me-2" type="button" data-toggle="modal" data-target="#importFile">Import Excel</a>
                                    <a href="{{ route('admin.quiz.question.destroy.all', $quiz_id) }}" class="btn btn-danger text-white">Delete All</a>
                                    <a href="{{ route('admin.quiz.question.create', $quiz_id) }}" class="btn btn-primary">Add Question</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center col-1">#</th>
                                                <th class="col-2">Question</th>
                                                <th class="col-2">A - Option</th>
                                                <th class="col-2">B - Option</th>
                                                <th class="col-2">C - Option</th>
                                                <th class="col-1">Correct Answer</th>
                                                <th class="col-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($questions as $question)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $question->question }}</td>
                                                    <td>{{ $question->a }}</td>
                                                    <td>{{ $question->b }}</td>
                                                    <td>{{ $question->c }}</td>
                                                    <td>{{ $question->correct_answer }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.quiz.question.language.index', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-language"></i>
                                                        </a>
                                                        <a href="{{ route('admin.quiz.question.edit', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.quiz.question.destroy', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="10"> No data found</td>
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
                    <form action="{{ route('admin.question.import.file', $quiz_id) }}" method="POST" enctype="multipart/form-data">
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
@section('scripts')
@endsection
