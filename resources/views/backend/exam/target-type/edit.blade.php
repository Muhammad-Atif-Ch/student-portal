@extends('backend.layouts.app')
@section('title', __('Edit Exam Type Target Type'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.exam.target-type.update', $response->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h4>Edit Exam Type Target Type</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Exam Type <small style="color: red">*</small></label>
                                                <select class="form-control" name="exam_type_id" required>
                                                    <option value="" disabled>Select Exam Type</option>
                                                    @foreach ($examTypes as $examType)
                                                        <option value="{{ $examType->id }}" {{ old('exam_type_id', $response->exam_type_id) == $examType->id ? 'selected' : '' }}>{{ $examType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Type <small style="color: red">*</small></label>
                                                <select class="form-control" name="type" required>
                                                    <option value="" disabled>Select Type</option>
                                                    @foreach (['car', 'bike', 'bus', 'truck'] as $type)
                                                        <option value="{{ $type }}" {{ old('type', $response->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                                    @endforeach
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
