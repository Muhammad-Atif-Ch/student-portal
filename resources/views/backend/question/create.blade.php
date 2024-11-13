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
                            <form action="{{ route('admin.test.question.store', $test_id) }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h4>Create Question</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question <small style="color: red">*</small></label>
                                                <input type="text" name="question" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Correct Answer <small style="color: red">*</small></label>
                                                <select class="form-control" name="correct_answer">
                                                    <option value="" selected>Select Option</option>
                                                    <option value="a">A - Option</option>
                                                    <option value="b">B - Option</option>
                                                    <option value="c">C - Option</option>
                                                    <option value="d">D - Option</option>
                                                    <option value="e">E - Option</option>
                                                    <option value="f">F - Option</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option <small style="color: red">*</small></label>
                                                <input type="text" name="a" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option <small style="color: red">*</small></label>
                                                <input type="text" name="b" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option <small style="color: red">*</small></label>
                                                <input type="text" name="c" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option <small style="color: red">*</small></label>
                                                <input type="text" name="d" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>E - Option</label>
                                                <input type="text" name="e" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>F - Option</label>
                                                <input type="text" name="f" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation</label>
                                                <textarea name="answer_explanation" class="form-control"></textarea>
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
