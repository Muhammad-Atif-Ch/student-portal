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
                                <h4>Questions List</h4>
                                <a href="{{ route('admin.quiz.question.create', $quiz_id) }}" class="btn btn-primary">Add Question</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Question</th>
                                                <th>A - Option</th>
                                                <th>B - Option</th>
                                                <th>C - Option</th>
                                                <th>D - Option</th>
                                                <th>E - Option</th>
                                                <th>F - Option</th>
                                                <th>Correct Answer</th>
                                                <th>Action</th>
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
                                                    <td>{{ $question->d }}</td>
                                                    <td>{{ $question->e }}</td>
                                                    <td>{{ $question->f }}</td>
                                                    <td>{{ $question->correct_answer }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.quiz.question.edit', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
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
    </div>
@endsection
@section('scripts')
@endsection
