@extends('backend.layouts.app')
@section('title', __('Add CPC Exam'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('admin.cpc.exam.store') }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h4>Create CPC Exam</h4>
                                </div>
                                <div class="card-body">
                                    @include('backend.cpc.exam._form')
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
