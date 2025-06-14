@extends('backend.layouts.app')
@section('title', __('Add Question'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.quiz.question.store', $quiz_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-header">
                                    <h4>Create Question</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question <small style="color: red">*</small></label>
                                                <input type="text" name="question" class="form-control" required value="{{ old('question') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Correct Answer <small style="color: red">*</small></label>
                                                <select class="form-control" name="correct_answer" value="{{ old('correct_answer') }}" required>
                                                    <option value="" selected>Select Option</option>
                                                    <option value="a" {{ old('correct_answer') == 'a' ? 'selected' : '' }}>A - Option</option>
                                                    <option value="b" {{ old('correct_answer') == 'b' ? 'selected' : '' }}>B - Option</option>
                                                    <option value="c" {{ old('correct_answer') == 'c' ? 'selected' : '' }}>C - Option</option>
                                                    <option value="d" {{ old('correct_answer') == 'd' ? 'selected' : '' }}>D - Option</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Type <small style="color: red">*</small></label>
                                                <select class="form-control" name="type" required>
                                                    <option value="" selected>Select Option</option>
                                                    <option value="car" {{ old('type') == 'car' ? 'selected' : '' }}>Car</option>
                                                    <option value="bike" {{ old('type') == 'bike' ? 'selected' : '' }}>Bike</option>
                                                    <option value="both" {{ old('type') == 'both' ? 'selected' : '' }}>Both</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question Translation </label>
                                                <input type="text" name="question_translation" class="form-control" value="{{ old('question_translation') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option <small style="color: red">*</small></label>
                                                <input type="text" name="a" class="form-control" required value="{{ old('a') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option <small style="color: red">*</small></label>
                                                <input type="text" name="b" class="form-control" required value="{{ old('b') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option</label>
                                                <input type="text" name="c" class="form-control" value="{{ old('c') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option</label>
                                                <input type="text" name="d" class="form-control" value="{{ old('d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option </label>
                                                <input type="text" name="a_translation" class="form-control" value="{{ old('a_translation') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option </label>
                                                <input type="text" name="b_translation" class="form-control" value="{{ old('b_translation') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option</label>
                                                <input type="text" name="c_translation" class="form-control" value="{{ old('c_translation') }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option</label>
                                                <input type="text" name="d_translation" class="form-control" value="{{ old('d_translation') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation</label>
                                                <textarea name="answer_explanation" class="form-control">{{ old('answer_explanation') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation _translation</label>
                                                <textarea name="answer_explanation_translation" class="form-control">{{ old('answer_explanation_translation') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Visual Explanation</label>
                                                <input type="file" name="visual_explanation" class="form-control" accept="image/*,video/*">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Choose Image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                        {{-- <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Choose Audio File</label>
                                                <input type="file" name="audio_file" class="form-control">
                                            </div>
                                        </div> --}}
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
