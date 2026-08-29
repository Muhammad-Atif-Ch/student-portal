@extends('backend.layouts.app')
@section('title', __('Apps'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    @foreach ($clientApps as $clientApp)
                        <div class="col-12 col-md-4 col-lg-4">
                            <div class="card">
                                <form action="{{ route('admin.client-apps.update', $clientApp->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-header">
                                        <h4>{{ __('App') }} {{ $loop->iteration }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>{{ __('Name') }}</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $clientApp->name) }}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{ __('Image') }}</label>
                                            <input type="file" name="image" class="form-control" accept="image/*">
                                            @if ($clientApp->image)
                                                <div class="mt-3">
                                                    <img src="{{ $clientApp->image_url }}" class="img-fluid form-control" style="max-width: 300px; height: 300px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button class="btn btn-primary mr-1" type="submit">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection
