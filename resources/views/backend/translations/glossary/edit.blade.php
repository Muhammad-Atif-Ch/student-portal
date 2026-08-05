@extends('backend.layouts.app')
@section('title', __('Edit Glossary Term'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.translations.glossary.update', $translationGlossary->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="card-header">
                                    <h4>Edit Glossary Term</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Source Term <small style="color: red">*</small></label>
                                                <input type="text" name="source_term" class="form-control" required value="{{ $translationGlossary->source_term }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Language <small style="color: red">*</small></label>
                                                <select class="form-control" name="language_id">
                                                    <option value="" selected>Select Option</option>
                                                    @foreach ($languages as $language)
                                                        <option value="{{ $language->id }}" {{ old('language_id', $language->id) == $translationGlossary->language_id ? 'selected' : '' }}>
                                                            {{ $language->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Target Term <small style="color: red">*</small></label>
                                                <input type="text" name="target_term" class="form-control" required value="{{ $translationGlossary->target_term }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button class="btn btn-primary mr-1" type="submit">Update</button>
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