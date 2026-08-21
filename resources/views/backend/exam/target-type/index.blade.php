@extends('backend.layouts.app')
@section('title', __('Exam Type Target Type List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Exam Type Target Types</h4>
                                <div>
                                    <a href="{{ route('admin.exam.target-type.create') }}" class="btn btn-primary">Add Target Type</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-questions">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Exam Type</th>
                                                <th>Type</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($targetTypes as $targetType)
                                                <tr>
                                                    <td>{{ $targetType->id }}</td>
                                                    <td>{{ $targetType->examType->name ?? '-' }}</td>
                                                    <td>{{ ucfirst($targetType->type) }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.exam.target-type.edit', $targetType->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.exam.target-type.destroy', $targetType->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $targetTypes->links('pagination::bootstrap-5') }}
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
