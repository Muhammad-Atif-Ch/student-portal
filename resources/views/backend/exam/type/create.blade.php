@extends('backend.layouts.app')
@section('title', __('Add Exam Type'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.exam.type.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-header">
                                    <h4>Create Exam Type</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Name <small style="color: red">*</small></label>
                                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                                            </div>
                                        </div>
                                        {{-- <div class="col-12 col-md-4 col-lg-4">
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
                                        </div> --}}

                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Total Questions </label>
                                                <input type="number" name="total_questions" class="form-control" value="{{ old('total_questions') }}" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Passing Marks </label>
                                                <input type="number" name="passing_marks" class="form-control" value="{{ old('passing_marks') }}" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Total Time (minutes) </label>
                                                <input type="number" name="total_time_minutes" class="form-control" value="{{ old('total_time_minutes') }}" min="0">
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
