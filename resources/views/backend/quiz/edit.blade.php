@extends('backend.layouts.app')
@section('title', __('Dashboard'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.quiz.update', $test->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h4>Edit Test</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Title <small style="color: red">*</small></label>
                                                <input type="text" name="title" class="form-control" required value="{{ $test->title}}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Per Question Mark <small style="color: red">*</small></label>
                                                <input type="number" name="per_question_mark" class="form-control" required value="{{ $test->per_question_mark}}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Test Time (per minutes) <small style="color: red">*</small></label>
                                                <input type="number" name="test_time" class="form-control" required value="{{ $test->test_time }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" class="form-control">{{ $test->description }}</textarea>
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
