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
                                            <label>Name</label>
                                            <input type="text" class="form-control" readonly value="{{ $contact_us->name }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" readonly value="{{ $contact_us->email }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Created At </label>
                                            <input type="text" class="form-control" readonly value="{{ $contact_us->created_at }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Platform </label>
                                            <input type="text" class="form-control" readonly value="{{ !empty($contact_us->platform) ? $contact_us->platform : 'N/A' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label>Device Id</label>
                                            <input type="text" class="form-control" readonly value="{{ $contact_us->user->device_id ?? 'N/A' }}">
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
                                            <textarea readonly class="form-control" rows="50">{{ $contact_us->message }}</textarea>
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
