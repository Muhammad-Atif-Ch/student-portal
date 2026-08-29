@extends('backend.layouts.app')
@section('title', __('Technical Dictionary'))
@section('style')
    <style>
        .td-audio-player {
            width: 100%;
            max-width: 260px;
            min-width: 240px;
            height: 40px;
        }

        .td-no-audio-badge {
            display: inline-block;
            font-size: 12px;
            color: #adb5bd;
            background: #f1f3f5;
            border-radius: 4px;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .td-regenerate-btn.is-loading .fa-sync-alt {
            animation: td-spin 0.8s linear infinite;
        }

        @keyframes td-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .td-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
@endsection
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Technical Dictionary</h4>
                                <div>
                                    <a href="{{ route('admin.technical-dictionary.create') }}" class="btn btn-primary">Add Term</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Image</th>
                                                <th>Term</th>
                                                <th>Explanation</th>
                                                <th>Translations</th>
                                                <th class="col-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($technicalDictionaries as $item)
                                                <tr>
                                                    <td>{{ $item->id }}</td>
                                                    <td>
                                                        @if ($item->image_url)
                                                            <img src="{{ $item->image_url }}" class="td-thumb" alt="">
                                                        @else
                                                            <span class="text-muted small">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->term }}</td>
                                                    <td>{{ Str::limit($item->explanation, 80) }}</td>
                                                    <td style="max-width: 260px;">
                                                        @forelse ($item->translations as $translation)
                                                            <span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">
                                                                {{ $translation->language->name ?? 'N/A' }}
                                                            </span>
                                                        @empty
                                                            <span class="text-muted small">No translations yet</span>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.technical-dictionary.edit', $item->id) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#technicalDictionaryTranslationModal-{{ $item->id }}">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                        <form action="{{ route('admin.technical-dictionary.destroy', $item->id) }}" method="POST" class="d-inline delete-form ml-2">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No technical dictionary terms found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    {{ $technicalDictionaries->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>

    {{-- One modal per term: regenerate-all button + per-language table with a single combined regenerate action --}}
    @foreach ($technicalDictionaries as $item)
        <div class="modal fade" id="technicalDictionaryTranslationModal-{{ $item->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Translations &amp; Audio &mdash; {{ $item->term }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm td-regenerate-all-btn" data-jobs='{{ json_encode($languages->map(fn ($language) => [
                                    "languageName" => $language->name,
                                    "url" => route("admin.technical-dictionary.regenerate", [$item->id, $language->id]),
                                ])->values()) }}'>
                                Generate All Active Languages
                            </button>
                            <div class="td-regenerate-all-progress mt-2" style="display: none; max-width: 460px;">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary td-regenerate-all-bar" role="progressbar" style="width: 0%;">0%</div>
                                </div>
                                <div class="small text-muted mt-1 td-regenerate-all-text"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Language</th>
                                        <th>Status</th>
                                        <th>Explanation Translation</th>
                                        <th>Audio</th>
                                        <th class="text-center">Regenerate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($languages as $language)
                                        @php
                                            $translation = $item->translations->firstWhere('language_id', $language->id);
                                        @endphp
                                        <tr data-language-row="{{ $language->id }}">
                                            <td>{{ $language->name }}</td>
                                            <td class="td-status-cell">
                                                @if ($translation)
                                                    <span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">{{ ucfirst($translation->status) }}</span>
                                                @else
                                                    <span class="badge badge-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td class="td-translation-cell">{{ $translation?->explanation_translation ?: '—' }}</td>
                                            <td class="td-audio-cell">
                                                @if ($translation?->explanation_audio_url)
                                                    <audio controls src="{{ $translation->explanation_audio_url }}" class="td-audio-player"></audio>
                                                @else
                                                    <span class="td-no-audio-badge">No audio yet</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-success td-regenerate-btn" data-url="{{ route('admin.technical-dictionary.regenerate', [$item->id, $language->id]) }}" title="Regenerate translation and audio">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No active languages found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script>
        $(function() {
            function regenerateRow($btn, url) {
                var $row = $btn.closest('tr');

                return $.post(url, { _token: '{{ csrf_token() }}' })
                    .done(function(res) {
                        $row.find('.td-translation-cell').text(res.translation || '—');
                        $row.find('.td-status-cell').html('<span class="badge badge-success">Completed</span>');

                        if (res.audio_url) {
                            $row.find('.td-audio-cell').html('<audio controls src="' + res.audio_url + '" class="td-audio-player"></audio>');
                        }
                    });
            }

            $(document).on('click', '.td-regenerate-btn', function() {
                var $btn = $(this);
                if ($btn.hasClass('is-loading')) return;

                $btn.addClass('is-loading').prop('disabled', true);

                regenerateRow($btn, $btn.data('url'))
                    .done(function(res) {
                        showToast('success', res.message || 'Regenerated successfully.');
                    })
                    .fail(function(xhr) {
                        var message = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to regenerate.';
                        showToast('error', message);
                    })
                    .always(function() {
                        $btn.removeClass('is-loading').prop('disabled', false);
                    });
            });

            function runJobsSequentially($modal, jobs, index, failCount) {
                var $bar = $modal.find('.td-regenerate-all-bar');
                var $text = $modal.find('.td-regenerate-all-text');
                var $btn = $modal.find('.td-regenerate-all-btn');

                if (index >= jobs.length) {
                    $btn.prop('disabled', false).text('Generate All Active Languages');
                    $bar.css('width', '100%').text('100%');
                    $text.text('Done. ' + jobs.length + ' language(s) processed' + (failCount ? ', ' + failCount + ' failed.' : '.'));
                    showToast(failCount ? 'error' : 'success', failCount
                        ? failCount + ' language(s) failed to regenerate.'
                        : 'Translation and audio generated for all active languages.');
                    setTimeout(function() {
                        $modal.find('.td-regenerate-all-progress').fadeOut(400);
                    }, 1500);
                    return;
                }

                var job = jobs[index];
                var percent = Math.round((index / jobs.length) * 100);
                $bar.css('width', percent + '%').text(percent + '%');
                $text.text('(' + (index + 1) + '/' + jobs.length + ') ' + job.languageName + ': working...');

                var $row = $modal.find('tr[data-language-row]').filter(function() {
                    return $(this).find('.td-regenerate-btn').data('url') === job.url;
                });
                var $fakeBtn = $row.find('.td-regenerate-btn');

                regenerateRow($fakeBtn, job.url)
                    .fail(function() {
                        failCount++;
                    })
                    .always(function() {
                        runJobsSequentially($modal, jobs, index + 1, failCount);
                    });
            }

            $(document).on('show.bs.modal', '.modal', function() {
                var $modal = $(this);
                $modal.find('.td-regenerate-all-progress').hide();
                $modal.find('.td-regenerate-all-bar').css('width', '0%').text('0%');
            });

            $(document).on('hidden.bs.modal', '.modal', function() {
                $(this).find('audio').each(function() {
                    this.pause();
                    this.currentTime = 0;
                });
            });

            $(document).on('click', '.td-regenerate-all-btn', function() {
                var $btn = $(this);
                if ($btn.prop('disabled')) return;

                var jobs = $btn.data('jobs') || [];
                var $modal = $btn.closest('.modal');

                if (!jobs.length) {
                    showToast('error', 'No active languages found.');
                    return;
                }

                $modal.find('.td-regenerate-all-progress').show();
                $modal.find('.td-regenerate-all-bar').css('width', '0%').text('0%');
                $btn.prop('disabled', true).text('Working...');

                runJobsSequentially($modal, jobs, 0, 0);
            });
        });
    </script>
@endpush
