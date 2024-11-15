@extends('backend.layouts.app')
@section('title', __('Tests'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Tests List</h4>
                                <a href="{{ route('admin.test.create') }}" class="btn btn-primary">Add Test</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th class="text-center">
                                                    #
                                                </th>
                                                <th>Title</th>
                                                <th>Per Question Marks</th>
                                                <th>Test Time</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($tests as $test)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $test->title }}</td>
                                                    <td>{{ $test->per_question_mark }}</td>
                                                    <td>{{ $test->test_time }}</td>
                                                    <td>{{ $test->description }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.test.edit', $test->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                                        <a href="{{ route('admin.test.question.index', $test->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                                        <form action="{{ route('admin.test.destroy', $test->id) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="4"> No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
@section('scripts')
@endsection
