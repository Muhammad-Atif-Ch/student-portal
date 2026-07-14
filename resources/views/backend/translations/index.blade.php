@extends('backend.layouts.app')
@section('title', __('Question Translations'))

@section('style')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 80px;
            display: inline-block;
        }

        /* ---- Summary table ---- */
        .status-badge {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .field-dots {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .field-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            background: #dee2e6;
            color: #6c757d;
            cursor: default;
        }

        .field-dot.is-done {
            background: #28a745;
            color: #fff;
        }

        .field-dot.has-audio-ring {
            box-shadow: 0 0 0 2px #007bff inset;
        }

        /* ---- Detail modal ---- */
        #translationDetailModal .modal-dialog {
            width: min(96vw, 1400px) !important;
            max-width: min(96vw, 1400px) !important;
            margin: 1.25rem auto !important;
        }

        #translationDetailModal .modal-body {
            max-height: 78vh;
            overflow-y: auto;
            padding: 1rem 1.25rem;
        }

        #translationDetailModal .table-responsive {
            overflow-x: hidden;
            width: 100%;
        }

        #modalFieldsTable {
            table-layout: fixed;
            width: 100%;
            margin-bottom: 0;
        }

        #modalFieldsTable td,
        #modalFieldsTable th {
            vertical-align: middle;
        }

        #modalFieldsTable .field-name-cell {
            font-weight: 600;
            white-space: nowrap;
            width: 13%;
        }

        #modalFieldsTable .translation-cell {
            direction: rtl;
            text-align: right;
            width: 50%;
        }

        #modalFieldsTable .audio-cell {
            width: 20%;
        }

        #modalFieldsTable .translate-action-cell,
        #modalFieldsTable .voice-action-cell {
            width: 7%;
            text-align: center;
        }

        .action-btn-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
        }

        .retranslate-btn,
        .regenerate-audio-btn {
            line-height: 1;
            padding: 0.25rem 0.45rem;
        }

        .field-translation-text {
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.55;
            background: #f8f9fa;
            border-radius: 4px;
            padding: 8px 12px;
            min-height: 38px;
            margin: 0;
        }

        .field-translation-text.is-empty {
            color: #adb5bd;
            font-style: italic;
        }

        .audio-player {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            height: 32px;
        }

        .no-audio-badge {
            display: inline-block;
            font-size: 12px;
            color: #adb5bd;
            background: #f1f3f5;
            border-radius: 4px;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .retranslate-btn .fa-sync-alt,
        .regenerate-audio-btn .fa-volume-up {
            transition: transform 0.4s ease;
        }

        .retranslate-btn.is-loading .fa-sync-alt,
        .regenerate-audio-btn.is-loading .fa-volume-up {
            animation: retranslate-spin 0.8s linear infinite;
        }

        @keyframes retranslate-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 767px) {
            #translationDetailModal .modal-dialog {
                width: 98vw !important;
                max-width: 98vw !important;
                margin: 0.5rem auto !important;
            }

            #modalFieldsTable .field-name-cell {
                white-space: normal;
            }
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>{{ __('Question Translations') }}</h4>
                                <div>
                                    <a href="{{ route('admin.translations.create') }}" class="btn btn-danger text-white">Create Translation</a>
                                    <a href="{{ route('admin.translations.createTts') }}" class="btn btn-primary">Create
                                        Text To Speach</a>
                                </div>
                            </div>
                            <div class="card-body" id="translationsApp" data-audio-base="{{ asset('audios') }}">
                                <div>
                                    <form method="post" action="{{ route('admin.translations.index') }}">
                                        @csrf
                                        @method('get')
                                        <div class="row">
                                            <div class="col-12 col-md-2 col-lg-2">
                                                <div class="form-group">
                                                    <label>Quiz ID</label>
                                                    <input type="text" name="quiz_id" class="form-control" placeholder="Quiz ID" value="{{ old('quiz_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-2 col-lg-2">
                                                <div class="form-group">
                                                    <label>Question ID</label>
                                                    <input type="text" name="question_id" class="form-control" placeholder="Question ID" value="{{ old('question_id') }}">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Language ID </label>
                                                    <select class="form-control" name="language_id">
                                                        <option value="" selected>Select Option</option>
                                                        @foreach ($languages as $language)
                                                            <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                                                {{ $language->name }}, {{ $language->code }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Status </label>
                                                    <select class="form-control" name="status">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed
                                                        </option>
                                                        <option value="partial" {{ old('status') == 'partial' ? 'selected' : '' }}>Partial
                                                        </option>
                                                        <option value="error" {{ old('status') == 'error' ? 'selected' : '' }}>Errored
                                                        </option>
                                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-2 col-lg-2">
                                                <div class="form-group">
                                                    <label> </label>
                                                    <button class="form-control btn btn-primary mt-2" type="submit">Search</button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="table-responsive mt-4">
                                    <table class="table table-striped" id="table-translations">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Quiz') }}</th>
                                                <th>{{ __('Question') }}</th>
                                                <th>{{ __('Language') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Fields') }}</th>
                                                <th>{{ __('Audio') }}</th>
                                                <th>{{ __('Updated') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($translations as $data)
                                                @php
                                                    $fields = [
                                                        'question' => $data->question_translation,
                                                        'a' => $data->a_translation,
                                                        'b' => $data->b_translation,
                                                        'c' => $data->c_translation,
                                                        'd' => $data->d_translation,
                                                        'answer_explanation' => $data->answer_explanation_translation,
                                                    ];
                                                    $audios = [
                                                        'question' => $data->question_audio,
                                                        'a' => $data->a_audio,
                                                        'b' => $data->b_audio,
                                                        'c' => $data->c_audio,
                                                        'd' => $data->d_audio,
                                                        'answer_explanation' => $data->answer_explanation_audio,
                                                    ];
                                                    $fieldsTranslatedCount = collect($fields)->filter(fn($v) => !empty($v))->count();
                                                    $audioCount = collect($audios)->filter(fn($v) => !empty($v))->count();
                                                    $status = $data->status ?? 'pending';
                                                    $statusColors = [
                                                        'completed' => 'success',
                                                        'partial' => 'warning',
                                                        'error' => 'danger',
                                                        'pending' => 'secondary',
                                                    ];
                                                @endphp
                                                <tr data-row-id="{{ $data->id }}">
                                                    <td>{{ $data->id }}</td>
                                                    <td>{{ $data->quiz->id }}</td>
                                                    <td>{{ $data->question->id }}</td>
                                                    <td>{{ $data->language->name }} <span class="text-muted">({{ $data->language_id }})</span></td>
                                                    <td>
                                                        <span class="badge badge-{{ $statusColors[$status] ?? 'secondary' }} status-badge" title="{{ $data->error }}">
                                                            {{ ucfirst($status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="field-dots" title="{{ $fieldsTranslatedCount }} / 6 fields translated">
                                                            @foreach ($fields as $key => $value)
                                                                <span class="field-dot {{ $value ? 'is-done' : '' }}" data-field="{{ $key }}"
                                                                    title="{{ strtoupper($key) }}: {{ $value ? 'translated' : 'not translated' }}">
                                                                    {{ strtoupper(substr($key, 0, 1)) }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>{{ $audioCount }} / 6</td>
                                                    <td><small class="text-muted">{{ $data->updated_at?->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary view-translation-btn" data-translation-id="{{ $data->id }}"
                                                            data-quiz-id="{{ $data->quiz->id }}" data-question-id="{{ $data->question->id }}" data-language="{{ $data->language->name }}"
                                                            data-status="{{ $status }}" data-error="{{ $data->error }}" data-fields="{{ json_encode($fields) }}"
                                                            data-audios="{{ json_encode($audios) }}">
                                                            <i class="fas fa-eye"></i> {{ __('View') }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="9"> No data found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <!-- Pagination Links -->
                                    {{ $translations->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Detail modal: populated from the row's data-* attributes, no extra request needed --}}
    <div class="modal fade translation-modal" id="translationDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('Translation') }} #<span id="modalTranslationId"></span>
                        <small class="text-muted d-block" id="modalMeta"></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="modalFieldsTable">
                            <colgroup>
                                <col class="field-col">
                                <col class="translation-col">
                                <col class="audio-col">
                                <col class="translate-action-col">
                                <col class="voice-action-col">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Translation') }}</th>
                                    <th>{{ __('Audio') }}</th>
                                    <th>{{ __('Translate') }}</th>
                                    <th>{{ __('Voice') }}</th>
                                </tr>
                            </thead>
                            <tbody id="modalFieldsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
