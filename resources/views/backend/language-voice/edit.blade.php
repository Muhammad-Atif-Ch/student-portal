@extends('backend.layouts.app')
@section('title', __('Edit Language Voice'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.language.voice.update', ['language' => $languageId, 'id' => $languageVoice->id]) }}" method="POST" >
                                @csrf
                                @method('Patch')
                                <div class="card-header">
                                    <h4>Edit Language Voice</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Language <small style="color: red">*</small></label>
                                                <select class="form-control" name="language_id" required readonly>
                                                    <option value="{{ $languageVoice->language_id }}"" selected>{{ $language->name }}, {{ $language->code }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Gender <small style="color: red">*</small></label>
                                                <select class="form-control" name="gender" required>
                                                    <option value="" selected>Select Option</option>
                                                    <option value="Female" {{ $languageVoice->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                    <option value="Male" {{ $languageVoice->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- @dd($languageVoice->locale) --}}
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Locale <small style="color: red">*</small></label>
                                                <input type="text" name="locale" class="form-control" required value="{{ $languageVoice->locale }}"">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Name <small style="color: red">*</small></label>
                                                <input type="text" name="name" class="form-control" required value="{{ $languageVoice->name }}"">
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
