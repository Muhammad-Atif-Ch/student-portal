@extends('backend.layouts.app')
@section('title', __('CPC Type List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>CPC Types</h4>
                                <div>
                                    <a href="{{ route('admin.cpc.type.create') }}" class="btn btn-primary">Add CPC Type</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($cpcTypes as $cpcType)
                                                <tr>
                                                    <td>{{ $cpcType->id }}</td>
                                                    <td>{{ $cpcType->title }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.cpc.type.edit', $cpcType->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.cpc.type.destroy', $cpcType->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No CPC types found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $cpcTypes->links('pagination::bootstrap-5') }}
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
