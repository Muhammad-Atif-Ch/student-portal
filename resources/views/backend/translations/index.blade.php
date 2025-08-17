@extends('backend.layouts.app')
@section('title', __('Question Translations'))

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .translation-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group label {
            font-weight: 600;
        }

        .audio-player {
            width: 100%;
            margin-top: 10px;
        }
    </style>
@endpush
@section('style')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 80px;
            display: inline-block;
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
                                    <a href="{{ route('admin.translations.createTranslation') }}" class="btn btn-danger text-white">Create Translation</a>
                                    <a href="{{ route('admin.translations.createTts') }}" class="btn btn-primary">Create Text To Speach</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mt-2">
                                    <h5>{{ __('Search Translation') }}</h5>
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
                                                            <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>{{ $language->name }}, {{ $language->code }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Type </label>
                                                    <select class="form-control" name="type">
                                                        <option value="" selected>Select Option</option>
                                                        <option value="car" {{ old('type') == 'car' ? 'selected' : '' }}>Car</option>
                                                        <option value="bike" {{ old('type') == 'bike' ? 'selected' : '' }}>Bike</option>
                                                        <option value="both" {{ old('type') == 'both' ? 'selected' : '' }}>Both</option>
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
                                                <th>{{ __('Question') }}</th>
                                                <th>{{ __('A') }}</th>
                                                <th>{{ __('B') }}</th>
                                                <th>{{ __('C') }}</th>
                                                <th>{{ __('D') }}</th>
                                                <th>{{ __('Answer Explanation') }}</th>
                                                <th>{{ __('Question Audio') }}</th>
                                                <th>{{ __('A Audio') }}</th>
                                                <th>{{ __('B Audio') }}</th>
                                                <th>{{ __('C Audio') }}</th>
                                                <th>{{ __('D Audio') }}</th>
                                                <th>{{ __('Answer Explanation Audio') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($translations as $data)
                                                <tr>
                                                    <td>{{ $data->id }}</td>
                                                    <td>{{ $data->quiz->id }}</td>
                                                    <td>{{ $data->question->id }}</td>
                                                    <td>{{ $data->language_id }} , {{ $data->language->name }}</td>
                                                    <td>{{ $data->question_translation }}</td>
                                                    <td>{{ $data->a_translation }}</td>
                                                    <td>{{ $data->b_translation }}</td>
                                                    <td>{{ $data->c_translation }}</td>
                                                    <td>{{ $data->d_translation }}</td>
                                                    <td>{{ Str::limit($data->answer_explanation_translation, 150) }}</td>
                                                    <td>
                                                        @if ($data->question_audio && file_exists(public_path("audios/{$data->question_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->question_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->a_audio && file_exists(public_path("audios/{$data->a_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->a_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->b_audio && file_exists(public_path("audios/{$data->b_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->b_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->c_audio && file_exists(public_path("audios/{$data->c_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->c_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->d_audio && file_exists(public_path("audios/{$data->d_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->d_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($data->answer_explanation_audio && file_exists(public_path("audios/{$data->answer_explanation_audio}")))
                                                            <audio controls class="mt-2">
                                                                <source src="{{ asset("audios/{$data->answer_explanation_audio}") }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        @else
                                                            <span class="text-danger">Null</span>
                                                        @endif
                                                    </td>
                                                    {{-- <td>
                                                        <a href="{{ route('admin.quiz.question.language.index', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-language"></i>
                                                        </a>
                                                        <a href="{{ route('admin.quiz.question.edit', ['quiz' => $quiz_id, 'question' => $question->id]) }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.quiz.question.destroy', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST" class="d-inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td> --}}
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center" colspan="16"> No data found</td>
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

@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $('#table-translations').DataTable({
                paging: false, // Disable DataTables pagination since we're using Laravel's
                ordering: true,
                searching: true
            });
        });
    </script>
@endsection
