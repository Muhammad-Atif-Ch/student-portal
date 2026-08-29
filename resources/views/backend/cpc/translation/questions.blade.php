@extends('backend.layouts.app')
@section('title', __('CPC Question Translations'))
@section('style')
    <style>
        #questionFieldsModal .modal-dialog {
            width: min(96vw, 1400px) !important;
            max-width: min(96vw, 1400px) !important;
            margin: 1.25rem auto !important;
        }

        #questionFieldsModal .modal-body {
            max-height: 78vh;
            overflow-y: auto;
        }

        #questionFieldsTable {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        #questionFieldsTable td,
        #questionFieldsTable th {
            vertical-align: middle;
        }

        #questionFieldsTable .field-name-cell {
            font-weight: 600;
            width: 13%;
        }

        #questionFieldsTable .translation-cell {
            direction: ltr;
            text-align: left;
            width: 45%;
        }

        #questionFieldsTable .audio-cell {
            width: 22%;
        }

        #questionFieldsTable .translate-action-cell,
        #questionFieldsTable .voice-action-cell {
            width: 10%;
            text-align: center;
        }

        .cpc-field-translation-text {
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.55;
            background: #f8f9fa;
            border-radius: 4px;
            padding: 8px 12px;
            min-height: 38px;
            margin: 0;
        }

        .cpc-field-translation-text.is-empty {
            color: #adb5bd;
            font-style: italic;
        }

        .cpc-audio-player {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            height: 32px;
        }

        .cpc-no-audio-badge {
            display: inline-block;
            font-size: 12px;
            color: #adb5bd;
            background: #f1f3f5;
            border-radius: 4px;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .cpc-lang-badges {
            max-height: 90px;
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: flex-start;
        }

        .cpc-lang-badges .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 3px 7px;
        }

        .cpc-lang-badges-summary {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .cpc-retranslate-btn.is-loading .fa-language,
        .cpc-regenerate-audio-btn.is-loading .fa-volume-up {
            animation: cpc-spin 0.8s linear infinite;
        }

        @keyframes cpc-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
                                <h4>Question Translations &mdash; {{ $caseStudy->title }}</h4>
                                <a href="{{ route('admin.cpc.translation.index') }}" class="btn btn-secondary btn-sm">Back to Case Studies</a>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <button type="button" id="cpcTranslateAllBtn" class="btn btn-outline-primary btn-sm">Translate All Questions (All Active Languages)</button>

                                    <div id="cpcTranslateAllProgressWrap" class="mt-2" style="display: none; max-width: 520px;">
                                        <div class="progress" style="height: 20px;">
                                            <div id="cpcTranslateAllProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;">0%</div>
                                        </div>
                                        <div id="cpcTranslateAllProgressText" class="small text-muted mt-1"></div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Existing Translations</th>
                                                <th class="col-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($caseStudy->cpcQuestions as $question)
                                                <tr>
                                                    <td>{{ $question->id }}</td>
                                                    <td>{{ Str::limit($question->question, 100) }}</td>
                                                    <td style="max-width: 320px;">
                                                        @if ($question->translations->isEmpty())
                                                            <span class="text-muted small">No translations yet</span>
                                                        @else
                                                            @php
                                                                $completedCount = $question->translations->where('status', 'completed')->count();
                                                                $partialCount = $question->translations->where('status', 'partial')->count();
                                                            @endphp
                                                            <div class="cpc-lang-badges-summary">
                                                                <span class="badge badge-success">{{ $completedCount }} completed</span>
                                                                @if ($partialCount)
                                                                    <span class="badge badge-warning">{{ $partialCount }} partial</span>
                                                                @endif
                                                                <span class="text-muted">/ {{ $question->translations->count() }} languages</span>
                                                            </div>
                                                            <div class="cpc-lang-badges">
                                                                @foreach ($question->translations as $translation)
                                                                    <span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">
                                                                        {{ $translation->language->name ?? 'N/A' }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#questionTranslationModal-{{ $question->id }}">Manage</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No questions found for this case study</td>
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

    {{-- One modal per question: bulk actions + existing-language table + ad-hoc new-language action --}}
    @foreach ($caseStudy->cpcQuestions as $question)
        <div class="modal fade" id="questionTranslationModal-{{ $question->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Translations &amp; Audio &mdash; {{ Str::limit($question->question, 60) }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row align-items-end mb-3">
                            <div class="col-6">
                                <label class="small mb-1">Add / Re-translate a Language</label>
                                <select class="form-control form-control-sm cpc-language-select" data-modal="questionTranslationModal-{{ $question->id }}">
                                    @foreach ($languages as $language)
                                        <option value="{{ $language->id }}">{{ $language->name }} ({{ $language->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-cpc-action-templated" data-template="{{ route('admin.cpc.translation.questions.translate', [$caseStudy->id, $question->id, 0]) }}">Translate</button>
                                <button type="button" class="btn btn-outline-success btn-sm btn-cpc-action-templated" data-template="{{ route('admin.cpc.translation.questions.audio', [$caseStudy->id, $question->id, 0]) }}">Generate Audio</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Language</th>
                                        <th>Status</th>
                                        <th>Audio</th>
                                        <th class="col-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($question->translations as $translation)
                                        @php
                                            $optionTranslationsForLang = $question->options->flatMap(fn ($o) => $o->translations)->where('language_id', $translation->language_id)->keyBy('cpc_question_option_id');
                                            $textOptions = $question->options->where('type', 'text');
                                            $audioCount = ($translation->question_audio ? 1 : 0) + ($translation->answer_explanation_audio ? 1 : 0) + $optionTranslationsForLang->whereNotNull('option_audio')->count();
                                            $audioTotal = 1 + (filled($question->answer_explanation) ? 1 : 0) + $textOptions->count();

                                            $fieldsPayload = [[
                                                'kind' => 'question',
                                                'label' => 'Question',
                                                'translation' => $translation->question_translation,
                                                'audioUrl' => $translation->question_audio_url,
                                                'canTranslate' => true,
                                                'translateUrl' => route('admin.cpc.translation.questions.text.translate', [$caseStudy->id, $question->id, $translation->language_id]),
                                                'audioActionUrl' => route('admin.cpc.translation.questions.text.audio', [$caseStudy->id, $question->id, $translation->language_id]),
                                            ]];

                                            if (filled($question->answer_explanation)) {
                                                $fieldsPayload[] = [
                                                    'kind' => 'explanation',
                                                    'label' => 'Answer Explanation',
                                                    'translation' => $translation->answer_explanation_translation,
                                                    'audioUrl' => $translation->answer_explanation_audio_url,
                                                    'canTranslate' => true,
                                                    'translateUrl' => route('admin.cpc.translation.questions.explanation.translate', [$caseStudy->id, $question->id, $translation->language_id]),
                                                    'audioActionUrl' => route('admin.cpc.translation.questions.explanation.audio', [$caseStudy->id, $question->id, $translation->language_id]),
                                                ];
                                            }

                                            foreach ($question->options as $option) {
                                                $optionTranslation = $optionTranslationsForLang->get($option->id);
                                                $fieldsPayload[] = [
                                                    'kind' => 'option',
                                                    'optionId' => $option->id,
                                                    'label' => 'Option '.$option->option_key,
                                                    'translation' => $optionTranslation?->text_value_translation,
                                                    'audioUrl' => $optionTranslation?->option_audio_url,
                                                    'canTranslate' => $option->type === 'text',
                                                    'translateUrl' => route('admin.cpc.translation.questions.options.translate', [$caseStudy->id, $question->id, $option->id, $translation->language_id]),
                                                    'audioActionUrl' => route('admin.cpc.translation.questions.options.audio', [$caseStudy->id, $question->id, $option->id, $translation->language_id]),
                                                ];
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $translation->language->name ?? 'N/A' }}</td>
                                            <td><span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">{{ ucfirst($translation->status) }}</span></td>
                                            <td>{{ $audioCount }} / {{ $audioTotal }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary view-question-fields-btn"
                                                    data-question-id="{{ $question->id }}"
                                                    data-language="{{ $translation->language->name ?? 'N/A' }}"
                                                    data-title="{{ Str::limit($question->question, 60) }}"
                                                    data-fields="{{ json_encode($fieldsPayload) }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No translations yet</td>
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

    <script>
        window.cpcTranslateAllJobs = {!! json_encode(
            $caseStudy->cpcQuestions->flatMap(function ($question) use ($languages, $caseStudy) {
                return $languages->map(function ($language) use ($question, $caseStudy) {
                    return [
                        'questionLabel' => Str::limit($question->question, 40),
                        'languageName' => $language->name,
                        'translateUrl' => route('admin.cpc.translation.questions.translate', [$caseStudy->id, $question->id, $language->id]),
                        'audioUrl' => route('admin.cpc.translation.questions.audio', [$caseStudy->id, $question->id, $language->id]),
                    ];
                });
            })->values()
        ) !!};
    </script>

    {{-- Shared detail modal: question + explanation + options, populated from the row's data-* attributes --}}
    <div class="modal fade" id="questionFieldsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('Translations & Audio') }} &mdash; <span id="questionFieldsModalTitle"></span>
                        <small class="text-muted d-block" id="questionFieldsModalMeta"></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="questionFieldsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Translation') }}</th>
                                    <th>{{ __('Audio') }}</th>
                                    <th>{{ __('Translate') }}</th>
                                    <th>{{ __('Voice') }}</th>
                                </tr>
                            </thead>
                            <tbody id="questionFieldsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            function runAction($btn, url) {
                var originalText = $btn.text();
                $btn.prop('disabled', true).text('Working...');

                $.post(url, { _token: '{{ csrf_token() }}' })
                    .done(function(res) {
                        showToast('success', res.message || 'Done.');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1200);
                    })
                    .fail(function(xhr) {
                        var message = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Something went wrong.';
                        showToast('error', message);
                        $btn.prop('disabled', false).text(originalText);
                    });
            }

            function runJobsSequentially(jobs, index, failCount, $bar, $text, $btn, originalText) {
                if (index >= jobs.length) {
                    $btn.prop('disabled', false).text(originalText);
                    $bar.css('width', '100%').text('100%');
                    $text.text('Done. ' + jobs.length + ' job(s) processed' + (failCount ? ', ' + failCount + ' failed.' : '.'));
                    showToast(failCount ? 'error' : 'success', failCount
                        ? failCount + ' translation/audio job(s) failed. Check the question list for details.'
                        : 'Translated and generated audio for all questions in all active languages.');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                var job = jobs[index];
                var percent = Math.round((index / jobs.length) * 100);
                $bar.css('width', percent + '%').text(percent + '%');
                $text.text('(' + (index + 1) + '/' + jobs.length + ') ' + job.languageName + ' — ' + job.questionLabel + ': translating...');

                $.post(job.translateUrl, { _token: '{{ csrf_token() }}' })
                    .fail(function() {
                        failCount++;
                    })
                    .always(function() {
                        $text.text('(' + (index + 1) + '/' + jobs.length + ') ' + job.languageName + ' — ' + job.questionLabel + ': generating audio...');

                        $.post(job.audioUrl, { _token: '{{ csrf_token() }}' })
                            .fail(function() {
                                failCount++;
                            })
                            .always(function() {
                                runJobsSequentially(jobs, index + 1, failCount, $bar, $text, $btn, originalText);
                            });
                    });
            }

            $(document).on('click', '#cpcTranslateAllBtn', function() {
                var $btn = $(this);
                if ($btn.prop('disabled')) return;

                var jobs = window.cpcTranslateAllJobs || [];
                var originalText = $btn.text();

                if (!jobs.length) {
                    showToast('error', 'No questions or active languages found.');
                    return;
                }

                var $bar = $('#cpcTranslateAllProgressBar');
                var $text = $('#cpcTranslateAllProgressText');
                $('#cpcTranslateAllProgressWrap').show();
                $bar.css('width', '0%').text('0%');
                $btn.prop('disabled', true).text('Working...');

                runJobsSequentially(jobs, 0, 0, $bar, $text, $btn, originalText);
            });

            $(document).on('click', '.btn-cpc-action-templated', function() {
                var $btn = $(this);
                var $modal = $btn.closest('.modal');
                var languageId = $modal.find('.cpc-language-select').val();
                var url = $btn.data('template').replace(/\/0$/, '/' + languageId);
                runAction($btn, url);
            });

            function escapeHtml(str) {
                return $('<div>').text(str || '').html();
            }

            function fieldRowHtml(field, index) {
                var hasText = Boolean(field.translation && $.trim(field.translation).length);
                var canTranslate = field.canTranslate !== false;
                var hasAudio = Boolean(field.audioActionUrl);

                var translateCell = canTranslate
                    ? '<button type="button" class="btn btn-sm btn-outline-primary cpc-retranslate-btn" data-index="' + index + '" ' +
                      'title="' + (hasText ? 'Re-translate this field' : 'Translate this field') + '"><i class="fas fa-language"></i></button>'
                    : '<span class="text-muted small">&mdash;</span>';

                var voiceCell = '<span class="text-muted small">&mdash;</span>';
                if (hasAudio) {
                    voiceCell = '<button type="button" class="btn btn-sm btn-outline-success cpc-regenerate-audio-btn" data-index="' + index + '" ' +
                        'title="' + (hasText ? 'Regenerate voice for this field' : 'Translate text first') + '" ' + (hasText ? '' : 'disabled') + '>' +
                        '<i class="fas fa-volume-up"></i></button>';
                }

                var audioCell = field.audioUrl
                    ? '<audio controls src="' + field.audioUrl + '" class="cpc-audio-player"></audio>'
                    : '<span class="cpc-no-audio-badge">' + (hasAudio ? 'No audio yet' : 'N/A') + '</span>';

                return '<tr data-field-row="' + index + '">' +
                    '<td class="field-name-cell">' + escapeHtml(field.label) + '</td>' +
                    '<td class="translation-cell"><div class="cpc-field-translation-text' + (hasText ? '' : ' is-empty') + '">' +
                    (hasText ? escapeHtml(field.translation) : 'Not translated yet') + '</div></td>' +
                    '<td class="audio-cell">' + audioCell + '</td>' +
                    '<td class="translate-action-cell">' + translateCell + '</td>' +
                    '<td class="voice-action-cell">' + voiceCell + '</td>' +
                    '</tr>';
            }

            $(document).on('click', '.view-question-fields-btn', function() {
                var $btn = $(this);
                var fields = $btn.data('fields') || [];

                $btn.data('current-fields', fields);
                $('#questionFieldsModalTitle').text($btn.data('title'));
                $('#questionFieldsModalMeta').text('Language: ' + $btn.data('language'));

                var html = fields.map(fieldRowHtml).join('');
                $('#questionFieldsTableBody').html(html);
                $('#questionFieldsModal').data('source-btn', $btn).modal('show');
            });

            $(document).on('click', '.cpc-retranslate-btn', function() {
                var $btn = $(this);
                if ($btn.hasClass('is-loading')) return;

                var $sourceBtn = $('#questionFieldsModal').data('source-btn');
                var fields = $sourceBtn.data('current-fields');
                var index = $btn.data('index');
                var field = fields[index];
                var $row = $btn.closest('tr');

                $btn.addClass('is-loading').prop('disabled', true);

                $.post(field.translateUrl, { _token: '{{ csrf_token() }}' })
                    .done(function(res) {
                        field.translation = res.translation;
                        $row.find('.cpc-field-translation-text').removeClass('is-empty').text(res.translation);
                        $btn.attr('title', 'Re-translate this field');

                        var $voiceBtn = $row.find('.cpc-regenerate-audio-btn');
                        if ($voiceBtn.length) {
                            $voiceBtn.prop('disabled', false).attr('title', 'Regenerate voice for this field');
                        }

                        showToast('success', res.message || 'Translated successfully.');
                    })
                    .fail(function(xhr) {
                        var message = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to translate this field.';
                        showToast('error', message);
                    })
                    .always(function() {
                        $btn.removeClass('is-loading').prop('disabled', false);
                    });
            });

            $(document).on('click', '.cpc-regenerate-audio-btn', function() {
                var $btn = $(this);
                if ($btn.hasClass('is-loading') || $btn.prop('disabled')) return;

                var $sourceBtn = $('#questionFieldsModal').data('source-btn');
                var fields = $sourceBtn.data('current-fields');
                var index = $btn.data('index');
                var field = fields[index];
                var $row = $btn.closest('tr');

                $btn.addClass('is-loading').prop('disabled', true);

                $.post(field.audioActionUrl, { _token: '{{ csrf_token() }}' })
                    .done(function(res) {
                        field.audioUrl = res.audio_url;
                        $row.find('.audio-cell').html('<audio controls src="' + res.audio_url + '" class="cpc-audio-player"></audio>');
                        showToast('success', res.message || 'Audio generated successfully.');
                    })
                    .fail(function(xhr) {
                        var message = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) ? (xhr.responseJSON.error || xhr.responseJSON.message) : 'Failed to regenerate audio.';
                        showToast('error', message);
                    })
                    .always(function() {
                        $btn.removeClass('is-loading').prop('disabled', false);
                    });
            });
        });
    </script>
@endpush
