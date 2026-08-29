@extends('backend.layouts.app')
@section('title', __('CPC Exam List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>CPC Exams</h4>
                                <div>
                                    <a href="{{ route('admin.cpc.exam.create') }}" class="btn btn-primary">Add CPC Exam</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Mode</th>
                                                <th>Time (min)</th>
                                                <th>Questions</th>
                                                <th>Passing Score</th>
                                                <th>Min/Scenario</th>
                                                <th>Status</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($cpcExams as $cpcExam)
                                                <tr>
                                                    <td>{{ $cpcExam->id }}</td>
                                                    <td>{{ $cpcExam->title }}</td>
                                                    <td>{{ $cpcExam->type->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($cpcExam->mode) }}</td>
                                                    <td>{{ $cpcExam->total_time_minutes }}</td>
                                                    <td>{{ $cpcExam->total_questions }}</td>
                                                    <td>{{ $cpcExam->passing_score }}</td>
                                                    <td>{{ $cpcExam->min_marks_per_scenario ?? '-' }}</td>
                                                    <td>
                                                        @if ($cpcExam->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.cpc.exam.edit', $cpcExam->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.cpc.exam.destroy', $cpcExam->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center">No CPC exams found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $cpcExams->links('pagination::bootstrap-5') }}
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
