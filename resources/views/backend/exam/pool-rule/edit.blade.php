@extends('backend.layouts.app')
@section('title', __('Edit Exam Pool Rule'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.exam.pool-rule.update', $response->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-header">
                                    <h4>Edit Exam Pool Rule</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
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
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Quiz <small style="color: red">*</small></label>
                                                <select class="form-control" name="quiz_id" required>
                                                    <option value="" disabled>Select Quiz</option>
                                                    @foreach ($quizzes as $quiz)
                                                        <option value="{{ $quiz->id }}" {{ old('quiz_id', $response->quiz_id) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Pool Type <small style="color: red">*</small></label>
                                                <select class="form-control" name="pool_type" id="pool_type" required>
                                                    <option value="" disabled>Select Pool Type</option>
                                                    @foreach (['common', 'specific'] as $poolType)
                                                        <option value="{{ $poolType }}" {{ old('pool_type', $response->pool_type) == $poolType ? 'selected' : '' }}>{{ ucfirst($poolType) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Specific Type</label>
                                                <select class="form-control" name="specific_type" id="specific_type">
                                                    <option value="">Select Specific Type</option>
                                                    @foreach (['car', 'bike', 'bus', 'truck'] as $type)
                                                        <option value="{{ $type }}" {{ old('specific_type', $response->specific_type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Required Count <small style="color: red">*</small></label>
                                                <input type="number" name="required_count" class="form-control" value="{{ old('required_count', $response->required_count) }}" min="1" required>
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
@push('scripts')
    <script>
        $(function() {
            function toggleSpecificType() {
                var isSpecific = $('#pool_type').val() === 'specific';
                $('#specific_type').prop('required', isSpecific);
            }
            $('#pool_type').on('change', toggleSpecificType);
            toggleSpecificType();
        });
    </script>
@endpush
