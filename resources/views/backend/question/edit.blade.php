@extends('backend.layouts.app')
@section('title', __('Question'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.test.question.update', ['test' => $test_id, 'question' => $question->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h4>Edit Question</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question <small style="color: red">*</small></label>
                                                <input type="text" name="question" class="form-control" required value="{{ $question->question }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Correct Answer <small style="color: red">*</small></label>
                                                <select class="form-control" name="correct_answer">
                                                    <option value="" selected>Select Option</option>
                                                    <option value="a" @if($question->correct_answer == 'a') selected @endif>A - Option</option>
                                                    <option value="b" @if($question->correct_answer == 'b') selected @endif>B - Option</option>
                                                    <option value="c" @if($question->correct_answer == 'c') selected @endif>C - Option</option>
                                                    <option value="d" @if($question->correct_answer == 'd') selected @endif>D - Option</option>
                                                    <option value="e" @if($question->correct_answer == 'e') selected @endif>E - Option</option>
                                                    <option value="f" @if($question->correct_answer == 'f') selected @endif>F - Option</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option <small style="color: red">*</small></label>
                                                <input type="text" name="a" class="form-control" required value="{{ $question->a }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option <small style="color: red">*</small></label>
                                                <input type="text" name="b" class="form-control" required value="{{ $question->b }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option <small style="color: red">*</small></label>
                                                <input type="text" name="c" class="form-control" required value="{{ $question->c }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option <small style="color: red">*</small></label>
                                                <input type="text" name="d" class="form-control" required value="{{ $question->d }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>E - Option</label>
                                                <input type="text" name="e" class="form-control" required value="{{ $question->e }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>F - Option</label>
                                                <input type="text" name="f" class="form-control" required value="{{ $question->f }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation</label>
                                                <textarea name="answer_explanation" class="form-control">{{ $question->answer_explanation }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Choose Audio File</label>
                                                <input type="file" name="audio_file" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button class="btn btn-primary mr-1" type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection