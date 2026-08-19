@extends('backend.layouts.app')
@section('title', __('Exam Type List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Exam Types</h4>
                                <div>
                                    <a href="{{ route('admin.exam.type.create') }}" class="btn btn-primary">Add Exam Type</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-questions">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Total Questions</th>
                                                <th>Passing Marks</th>
                                                <th>Total Time (minutes)</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($examTypes as $examType)
                                                <tr>
                                                    <td>{{ $examType->id }}</td>
                                                    <td>{{ $examType->name }}</td>
                                                    <td>{{ $examType->total_questions }}</td>
                                                    <td>{{ $examType->passing_marks }}</td>
                                                    <td>{{ $examType->total_time_minutes }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.exam.type.edit', $examType->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.exam.type.destroy', $examType->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {{ $examTypes->links('pagination::bootstrap-5') }}
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
@push('scripts')
    <script>
        $(function() {
            // initSimpleDataTable('#table-questions', {
            //     pageLength: 100,
            //     lengthMenu: [10, 25, 50, 100, 200],
            //     language: {
            //         emptyTable: "No data found"
            //     }
            // });
            // if ($.fn.DataTable.isDataTable('#table-questions')) {
            //     $('#table-questions').DataTable().destroy();
            // }
            // $('#table-questions').DataTable({
            //     paging: false,
            //     ordering: true,
            //     searching: true
            // });
        });
    </script>
@endpush
