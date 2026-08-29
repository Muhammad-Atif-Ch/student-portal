@extends('backend.layouts.app')
@section('title', __('API Settings'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.setting.apiSettings.update') }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h4>API Settings</h4>
                                </div>
                                <div class="card-body">
                                    <h5>{{ __('Translation API') }}</h5>
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Key') }}</label>
                                                <input type="text" name="translation_api_key" class="form-control" value="{{ old('translation_api_key', $setting->translation_api_key) }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Region') }}</label>
                                                <input type="text" name="translation_api_region" class="form-control" value="{{ old('translation_api_region', $setting->translation_api_region) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <h5>{{ __('Text-To-Speech API') }}</h5>
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Key') }}</label>
                                                <input type="text" name="tts_api_key" class="form-control" value="{{ old('tts_api_key', $setting->tts_api_key) }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Region') }}</label>
                                                <input type="text" name="tts_api_region" class="form-control" value="{{ old('tts_api_region', $setting->tts_api_region) }}">
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
