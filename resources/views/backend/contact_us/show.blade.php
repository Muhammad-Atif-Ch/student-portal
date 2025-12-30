@extends('backend.layouts.app')
@section('title', __('Edit Contact Us'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Contact Us</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Name <small style="color: red">*</small></label>
                                            <input type="text" class="form-control" readonly value="{{ $contact_us->name }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Email <small style="color: red">*</small></label>
                                            <input type="email" class="form-control" readonly value="{{ $contact_us->email }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <input type="text" class="form-control" readonly value="{{ $contact_us->subject }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12">
                                        <div class="form-group">
                                            <label>Message</label>
                                            <textarea name="" id="" readonly class="form-control" rows="50">{{ $contact_us->message }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection
