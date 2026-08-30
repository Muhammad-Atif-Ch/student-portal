@extends('backend.layouts.app')
@section('title', __('CPC Question List'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>CPC Questions</h4>
                                <div>
                                    <a href="{{ route('admin.cpc.question.create') }}" class="btn btn-primary">Add CPC Question</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Case Study</th>
                                                <th>Correct Option</th>
                                                <th class="col-1">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($cpcQuestions as $cpcQuestion)
                                                <tr>
                                                    <td>{{ $cpcQuestion->id }}</td>
                                                    <td>{{ Str::limit($cpcQuestion->question, 100) }}</td>
                                                    <td>{{ $cpcQuestion->caseStudy->title ?? '-' }}</td>
                                                    <td>
                                                        @php $correct = $cpcQuestion->options->firstWhere('is_correct', true); @endphp
                                                        @if ($correct)
                                                            <span class="badge badge-success">{{ strtoupper($correct->option_key) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.cpc.question.edit', $cpcQuestion->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.cpc.question.destroy', $cpcQuestion->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No CPC questions found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $cpcQuestions->links('pagination::bootstrap-5') }}
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
