@extends('backend.layouts.app')
@section('title', __('CPC Translations'))
@section('style')
    <style>
        #caseStudyBlocksModal .modal-dialog {
            width: min(96vw, 1400px) !important;
            max-width: min(96vw, 1400px) !important;
            margin: 1.25rem auto !important;
        }

        #caseStudyBlocksModal .modal-body {
            max-height: 78vh;
            overflow-y: auto;
        }

        #caseStudyBlocksTable {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        #caseStudyBlocksTable td,
        #caseStudyBlocksTable th {
            vertical-align: middle;
        }

        #caseStudyBlocksTable .field-name-cell {
            font-weight: 600;
            width: 13%;
        }

        #caseStudyBlocksTable .translation-cell {
            direction: ltr;
            text-align: left;
            width: 45%;
        }

        #caseStudyBlocksTable .audio-cell {
            width: 22%;
        }

        #caseStudyBlocksTable .translate-action-cell,
        #caseStudyBlocksTable .voice-action-cell {
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
                            <div class="card-header">
                                <h4>CPC Translations</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Only languages that already have translation data are listed below. Open "Manage" to translate into a new language, re-translate, or generate audio.</p>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Blocks</th>
                                                <th>Questions</th>
                                                <th>Existing Translations</th>
                                                <th class="col-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($caseStudies as $caseStudy)
                                                <tr>
                                                    <td>{{ $caseStudy->id }}</td>
                                                    <td>{{ $caseStudy->title }}</td>
                                                    <td>{{ $caseStudy->blocks->count() }}</td>
                                                    <td>{{ $caseStudy->cpcQuestions->count() }}</td>
                                                    <td>
                                                        @forelse ($caseStudy->translations as $translation)
                                                            <span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">
                                                                {{ $translation->language->name ?? 'N/A' }}
                                                            </span>
                                                        @empty
                                                            <span class="text-muted small">No translations yet</span>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#caseStudyTranslationModal-{{ $caseStudy->id }}">Manage</button>
                                                        <a href="{{ route('admin.cpc.translation.questions', $caseStudy->id) }}" class="btn btn-secondary btn-sm">Questions</a>
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

    {{-- One modal per case study: bulk actions + existing-language table + ad-hoc new-language action --}}
    @foreach ($caseStudies as $caseStudy)
        <div class="modal fade" id="caseStudyTranslationModal-{{ $caseStudy->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Translations &amp; Audio &mdash; {{ $caseStudy->title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-cpc-action" data-url="{{ route('admin.cpc.case-study.translate-all', $caseStudy->id) }}">Translate All Languages</button>
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
                                    @forelse ($caseStudy->translations as $translation)
                                        @php
                                            $blockTranslationsForLang = $caseStudy->blocks->flatMap(fn ($b) => $b->translations)->where('language_id', $translation->language_id)->keyBy('cpc_case_study_block_id');
                                            $audioCount = $blockTranslationsForLang->whereNotNull('content_audio')->count();
                                            $fieldsPayload = [[
                                                'kind' => 'title',
                                                'label' => 'Title',
                                                'translation' => $translation->title_translation,
                                                'audioUrl' => null,
                                                'translateUrl' => route('admin.cpc.case-study.title.translate', [$caseStudy->id, $translation->language_id]),
                                            ]];
                                            foreach ($caseStudy->blocks->sortBy('sort_order') as $block) {
                                                $blockTranslation = $blockTranslationsForLang->get($block->id);
                                                $displayTranslation = $blockTranslation?->content_translation;
                                                if (! $displayTranslation && $block->type === 'list' && $blockTranslation?->items_translation) {
                                                    $displayTranslation = implode("\n", $blockTranslation->items_translation);
                                                }
                                                $fieldsPayload[] = [
                                                    'kind' => 'block',
                                                    'blockId' => $block->id,
                                                    'label' => 'Block '.($block->sort_order + 1).' ('.ucfirst($block->type).')',
                                                    'translation' => $displayTranslation,
                                                    'audioUrl' => $blockTranslation?->content_audio_url,
                                                    'canTranslate' => in_array($block->type, ['text', 'list'], true),
                                                    'translateUrl' => route('admin.cpc.case-study.blocks.translate', [$caseStudy->id, $block->id, $translation->language_id]),
                                                    'audioActionUrl' => route('admin.cpc.case-study.blocks.audio', [$caseStudy->id, $block->id, $translation->language_id]),
                                                ];
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $translation->language->name ?? 'N/A' }}</td>
                                            <td><span class="badge {{ $translation->status === 'completed' ? 'badge-success' : ($translation->status === 'partial' ? 'badge-warning' : 'badge-secondary') }}">{{ ucfirst($translation->status) }}</span></td>
                                            <td>{{ $audioCount }} / {{ $caseStudy->blocks->whereIn('type', ['text', 'list'])->count() }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary view-case-study-blocks-btn"
                                                    data-case-study-id="{{ $caseStudy->id }}"
                                                    data-language="{{ $translation->language->name ?? 'N/A' }}"
                                                    data-title="{{ $caseStudy->title }}"
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

    {{-- Shared detail modal: title + every block, populated from the row's data-* attributes --}}
    <div class="modal fade" id="caseStudyBlocksModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('Translations & Audio') }} &mdash; <span id="caseStudyBlocksModalTitle"></span>
                        <small class="text-muted d-block" id="caseStudyBlocksModalMeta"></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="caseStudyBlocksTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Translation') }}</th>
                                    <th>{{ __('Audio') }}</th>
                                    <th>{{ __('Translate') }}</th>
                                    <th>{{ __('Voice') }}</th>
                                </tr>
                            </thead>
                            <tbody id="caseStudyBlocksTableBody"></tbody>
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

            $(document).on('click', '.btn-cpc-action', function() {
                runAction($(this), $(this).data('url'));
            });

            function escapeHtml(str) {
                return $('<div>').text(str || '').html();
            }

            function fieldRowHtml(field, index) {
                var hasText = Boolean(field.translation && $.trim(field.translation).length);
                var isBlock = field.kind === 'block';
                var canTranslate = isBlock ? field.canTranslate !== false : true;

                var translateCell = canTranslate
                    ? '<button type="button" class="btn btn-sm btn-outline-primary cpc-retranslate-btn" data-index="' + index + '" ' +
                      'title="' + (hasText ? 'Re-translate this field' : 'Translate this field') + '"><i class="fas fa-language"></i></button>'
                    : '<span class="text-muted small">&mdash;</span>';

                var voiceCell = '<span class="text-muted small">&mdash;</span>';
                if (isBlock && canTranslate) {
                    voiceCell = '<button type="button" class="btn btn-sm btn-outline-success cpc-regenerate-audio-btn" data-index="' + index + '" ' +
                        'title="' + (hasText ? 'Regenerate voice for this block' : 'Translate text first') + '" ' + (hasText ? '' : 'disabled') + '>' +
                        '<i class="fas fa-volume-up"></i></button>';
                }

                var audioCell = field.audioUrl
                    ? '<audio controls src="' + field.audioUrl + '" class="cpc-audio-player"></audio>'
                    : '<span class="cpc-no-audio-badge">' + (isBlock ? 'No audio yet' : 'N/A') + '</span>';

                return '<tr data-field-row="' + index + '">' +
                    '<td class="field-name-cell">' + escapeHtml(field.label) + '</td>' +
                    '<td class="translation-cell"><div class="cpc-field-translation-text' + (hasText ? '' : ' is-empty') + '">' +
                    (hasText ? escapeHtml(field.translation) : 'Not translated yet') + '</div></td>' +
                    '<td class="audio-cell">' + audioCell + '</td>' +
                    '<td class="translate-action-cell">' + translateCell + '</td>' +
                    '<td class="voice-action-cell">' + voiceCell + '</td>' +
                    '</tr>';
            }

            $(document).on('click', '.view-case-study-blocks-btn', function() {
                var $btn = $(this);
                var fields = $btn.data('fields') || [];

                $btn.data('current-fields', fields);
                $('#caseStudyBlocksModalTitle').text($btn.data('title'));
                $('#caseStudyBlocksModalMeta').text('Language: ' + $btn.data('language'));

                var html = fields.map(fieldRowHtml).join('');
                $('#caseStudyBlocksTableBody').html(html);
                $('#caseStudyBlocksModal').data('source-btn', $btn).modal('show');
            });

            $(document).on('click', '.cpc-retranslate-btn', function() {
                var $btn = $(this);
                if ($btn.hasClass('is-loading')) return;

                var $sourceBtn = $('#caseStudyBlocksModal').data('source-btn');
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
                            $voiceBtn.prop('disabled', false).attr('title', 'Regenerate voice for this block');
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

                var $sourceBtn = $('#caseStudyBlocksModal').data('source-btn');
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
