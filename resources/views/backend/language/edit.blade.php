@extends('backend.layouts.app')
@section('title', __('Edit Language'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.language.update', $language->id) }}" method="POST">
                                @csrf
                                @method('Patch')
                                <div class="card-header">
                                    <h4>Edit Language</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Family <small style="color: red">*</small></label>
                                                <input type="text" name="family" class="form-control" required value="{{ $language->family }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Name <small style="color: red">*</small></label>
                                                <input type="text" name="name" class="form-control" required value="{{ $language->name }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Native Name <small style="color: red">*</small></label>
                                                <input type="text" name="native_name" class="form-control" required value="{{ $language->native_name }}">
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Code</label>
                                                <input type="text" name="code" class="form-control" value="{{ $language->code }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Code 2</label>
                                                <input type="text" name="code_2" class="form-control" value="{{ $language->code_2 }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Show</label>
                                               <select name="show" class="form-control">
                                                   <option value="0" {{ $language->show == 0 ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ $language->show == 1 ? 'selected' : '' }}>Yes</option>
                                                </select>
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
