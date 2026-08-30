@extends('backend.layouts.app')
@section('title', __('Edit CPC Case Study'))
@section('style')
    <link rel="stylesheet" href="{{ asset('assets/bundles/summernote/summernote-bs4.css') }}">
@endsection
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('admin.cpc.case-study.update', $response->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="card-header">
                                    <h4>Edit CPC Case Study</h4>
                                </div>
                                <div class="card-body">
                                    @include('backend.cpc.case-study._form')
                                </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('admin.cpc.case-study.show', $response->id) }}" class="btn btn-secondary mr-1" target="_blank">Preview</a>
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
