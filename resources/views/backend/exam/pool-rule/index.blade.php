@extends('backend.layouts.app')
@section('title', __('Exam Pool Rule List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Exam Pool Rules</h4>
                                <div>
                                    <a href="{{ route('admin.exam.pool-rule.create') }}" class="btn btn-primary">Add Pool Rule</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-questions">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Exam Type</th>
                                                <th>Quiz</th>
                                                <th>Pool Type</th>
                                                <th>Specific Type</th>
                                                <th>Required Count</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($poolRules as $poolRule)
                                                <tr>
                                                    <td>{{ $poolRule->id }}</td>
                                                    <td>{{ $poolRule->examType->name ?? '-' }}</td>
                                                    <td>{{ $poolRule->quiz->title ?? '-' }}</td>
                                                    <td>{{ ucfirst($poolRule->pool_type) }}</td>
                                                    <td>{{ $poolRule->specific_type ? ucfirst($poolRule->specific_type) : '-' }}</td>
                                                    <td>{{ $poolRule->required_count }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.exam.pool-rule.edit', $poolRule->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.exam.pool-rule.destroy', $poolRule->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $poolRules->links('pagination::bootstrap-5') }}
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
