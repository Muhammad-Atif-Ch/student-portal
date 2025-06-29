@extends('backend.layouts.app')
@section('title', __('App Image'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form id="questionForm" action="{{ route('admin.setting.appImage.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                {{-- @method('PUT') --}}
                                <div class="card-header">
                                    <h4>App Image Update</h4>
                                </div>
                                <div class="card-body">
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*,video/*">
                                                {{-- <img src="{{ asset("images/{$question->visual_explanation}") }}" class="img-fluid form-control mt-3" style="max-width: 300px;height: 300px;"> --}}
                                                @if ($app->image)
                                                    @php
                                                        // Get file extension
                                                        $extension = pathinfo($app->image, PATHINFO_EXTENSION);
                                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                                        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];
                                                    @endphp

                                                    <div class="mt-3">
                                                        @if (in_array($extension, $imageExtensions))
                                                            {{-- Show image --}}
                                                            <img src="{{ asset("images/{$app->image}") }}" class="img-fluid form-control" style="max-width: 300px; height: 300px;">
                                                        @endif
                                                    </div>
                                                @endif
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
