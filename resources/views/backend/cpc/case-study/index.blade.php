@extends('backend.layouts.app')
@section('title', __('CPC Case Study List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>CPC Case Studies</h4>
                                <div>
                                    <a href="{{ route('admin.cpc.case-study.create') }}" class="btn btn-primary">Add Case Study</a>
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
                                                <th>Blocks</th>
                                                <th>Questions</th>
                                                <th class="col-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($caseStudies as $caseStudy)
                                                <tr>
                                                    <td>{{ $caseStudy->id }}</td>
                                                    <td>{{ $caseStudy->title }}</td>
                                                    <td>{{ $caseStudy->type->title ?? '-' }}</td>
                                                    <td>{{ $caseStudy->blocks->count() }}</td>
                                                    <td>{{ $caseStudy->cpcQuestions->count() }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.cpc.case-study.show', $caseStudy->id) }}" class="btn btn-secondary btn-sm" title="Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.cpc.case-study.edit', $caseStudy->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.cpc.case-study.destroy', $caseStudy->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No case studies found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $caseStudies->links('pagination::bootstrap-5') }}
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
