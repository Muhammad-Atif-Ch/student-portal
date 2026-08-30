@extends('backend.layouts.app')
@section('title', __('Preview CPC Case Study'))
@section('style')
    <style>
        .cs-preview p, .cs-preview div, .cs-preview li, .cs-preview span { font-size: 16px; line-height: 1.6; }
        .cs-preview h1 { font-size: 1.6em; font-weight: 700; }
        .cs-preview h2 { font-size: 1.4em; font-weight: 700; }
        .cs-preview h3 { font-size: 1.25em; font-weight: 700; }
        .cs-preview h4 { font-size: 1.1em; font-weight: 700; }
        .cs-preview h5, .cs-preview h6 { font-size: 1em; font-weight: 700; }
    </style>
@endsection
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-lg-9">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Preview</h4>
                                <a href="{{ route('admin.cpc.case-study.edit', $response->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            </div>
                            <div class="card-body cs-preview">
                                <h1>{{ $response->title }}</h1>
                                <p class="text-muted">Type: {{ $response->type->title ?? '-' }}</p>

                                @foreach ($response->blocks as $block)
                                    @if ($block->type === 'text')
                                        <div>{!! $block->content !!}</div>
                                    @elseif ($block->type === 'image' && $block->file_path)
                                        <div class="my-3">
                                            <img src="{{ $block->file_url }}" alt="" class="img-fluid rounded">
                                        </div>
                                    @elseif ($block->type === 'list')
                                        @if ($block->list_style === 'numbered')
                                            <ol>
                                                @foreach ($block->items ?? [] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ol>
                                        @else
                                            <ul>
                                                @foreach ($block->items ?? [] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if ($response->cpcQuestions->isNotEmpty())
                            <div class="card">
                                <div class="card-header"><h4>Attached Questions</h4></div>
                                <div class="card-body">
                                    <ol>
                                        @foreach ($response->cpcQuestions as $question)
                                            <li class="mb-2">{{ $question->question }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection
