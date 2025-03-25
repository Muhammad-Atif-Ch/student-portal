@extends('backend.layouts.app')
@section('title', __('Questions Language List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Question Language List</h4>
                                <div>
                                    <a href="{{ route('admin.quiz.question.language.create', ['quiz' => $quiz_id, 'question' => $question_id]) }}" class="btn btn-primary">Add Question Lenguage</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center col-1">#</th>
                                                <th class="col-2">Quiz</th>
                                                <th class="col-2">Question</th>
                                                <th class="col-2">Lenguage</th>
                                                <th class="col-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($questions_lenguage as $data)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $data->quiz->title }}</td>
                                                    <td>{{ $data->question->question }}</td>
                                                    <td>{{ $data->language->name }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.quiz.question.language.edit', ['quiz' => $quiz_id, 'question' => $question_id, 'language' => $data->id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.quiz.question.language.destroy', ['quiz' => $quiz_id, 'question' => $question_id, 'language' => $data->id]) }}" method="POST"
                                                            class="d-inline">
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
    </div>
@endsection
@section('scripts')
@endsection
