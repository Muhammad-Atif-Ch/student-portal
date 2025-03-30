@extends('backend.layouts.app')
@section('title', __('Add Question Translation'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.quiz.question.language.store', ['quiz' => $quiz_id, 'question' => $question_id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-header">
                                    <h4>Add Question Translation</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Languages <small style="color: red">*</small></label>
                                                <select class="form-control" name="language_id" value="{{ old('language_id') }}" required>
                                                    <option value="" selected>Select Option</option>
                                                    @foreach ($languages as $language)
                                                        <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Title Audio File <small style="color: red">*</small></label>
                                                <input type="file" name="title_audio_file" class="form-control" accept="audio/*">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>A Audio File </label>
                                                <input type="file" name="a_audio_file" class="form-control" accept="audio/*">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>B Audio File</label>
                                                <input type="file" name="b_audio_file" class="form-control" accept="audio/*">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>C Audio File</label>
                                                <input type="file" name="c_audio_file" class="form-control" accept="audio/*">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>D Audio File</label>
                                                <input type="file" name="d_audio_file" class="form-control" accept="audio/*">
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
