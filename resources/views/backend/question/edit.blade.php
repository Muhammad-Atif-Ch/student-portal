@extends('backend.layouts.app')
@section('title', __('Question Update'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.quiz.question.update', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="card-header">
                                    <h4>{{ $quiz->id }} - {{ $quiz->title }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question <small style="color: red">*</small></label>
                                                <input type="text" name="question" class="form-control" required value="{{ $question->question }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Correct Answer <small style="color: red">*</small></label>
                                                <select class="form-control" name="correct_answer">
                                                    <option value="" selected>Select Option</option>
                                                    <option value="a" @if ($question->correct_answer == 'a') selected @endif>A - Option</option>
                                                    <option value="b" @if ($question->correct_answer == 'b') selected @endif>B - Option</option>
                                                    <option value="c" @if ($question->correct_answer == 'c') selected @endif>C - Option</option>
                                                    <option value="d" @if ($question->correct_answer == 'd') selected @endif>D - Option</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Type <small style="color: red">*</small></label>
                                                <select class="form-control select2" name="type[]" multiple>
                                                    <option value="car" {{ in_array('car', old('type', $question->type)) ? 'selected' : '' }}>Car</option>
                                                    <option value="bike" {{ in_array('bike', old('type', $question->type)) ? 'selected' : '' }}>Bike</option>
                                                    <option value="bus" {{ in_array('bus', old('type', $question->type)) ? 'selected' : '' }}>Bus</option>
                                                    <option value="truck" {{ in_array('truck', old('type', $question->type)) ? 'selected' : '' }}>Truck</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Question Translation </label>
                                                <input type="text" name="question_translation" class="form-control" value="{{ $question->question_translation }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option <small style="color: red">*</small></label>
                                                <input type="text" name="a" class="form-control" required value="{{ $question->a }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option <small style="color: red">*</small></label>
                                                <input type="text" name="b" class="form-control" required value="{{ $question->b }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option</label>
                                                <input type="text" name="c" class="form-control" value="{{ $question->c }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option </label>
                                                <input type="text" name="d" class="form-control" value="{{ $question->d }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>A - Option Translation </label>
                                                <input type="text" name="a_translation" class="form-control" value="{{ $question->a_translation }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>B - Option Translation </label>
                                                <input type="text" name="b_translation" class="form-control" value="{{ $question->b_translation }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>C - Option Translation </label>
                                                <input type="text" name="c_translation" class="form-control" value="{{ $question->c_translation }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3">
                                            <div class="form-group">
                                                <label>D - Option Translation </label>
                                                <input type="text" name="d_translation" class="form-control" value="{{ $question->d_translation }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation</label>
                                                <textarea name="answer_explanation" class="form-control">{{ $question->answer_explanation }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12">
                                            <div class="form-group">
                                                <label>Answer Explanation Translation</label>
                                                <textarea name="answer_explanation_translation" class="form-control">{{ $question->answer_explanation_translation }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Visual Explanation</label>
                                                <input type="file" name="visual_explanation" class="form-control" accept="image/*,video/*">
                                                {{-- <img src="{{ asset("images/{$question->visual_explanation}") }}" class="img-fluid form-control mt-3" style="max-width: 300px;height: 300px;"> --}}
                                                @if ($question->visual_explanation)
                                                    @php
                                                        // Get file extension
                                                        $extension = pathinfo($question->visual_explanation, PATHINFO_EXTENSION);
                                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                                        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];
                                                        $visualPath = public_path('images/' . $question->visual_explanation);
                                                    @endphp

                                                    <div class="mt-3">
                                                        @if (in_array($extension, $imageExtensions))
                                                            {{-- Show image --}}
                                                            <img id="visual-explanation-image" src="{{ asset("images/{$question->visual_explanation}") }}" class="img-fluid form-control"
                                                                style="max-width: 300px; height: 300px;">
                                                            <button type="button" id="remove-visual-btn" class="btn btn-danger mt-2">Remove Visual</button>
                                                        @elseif (in_array($extension, $videoExtensions))
                                                            {{-- Show video --}}
                                                            <video id="visual-explanation-video" controls class="img-fluid form-control" style="max-width: 300px; height: 300px;">
                                                                <source src="{{ asset("images/{$question->visual_explanation}") }}" type="video/{{ $extension }}">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                            <button type="button" id="remove-visual-btn" class="btn btn-danger mt-2">Remove Visual</button>
                                                        @else
                                                            <p>File format not supported.</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Choose Image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                @php
                                                    $imagePath = public_path('images/' . $question->image);
                                                @endphp
                                                @if ($question->image && file_exists($imagePath))
                                                    <img id="question-image" src="{{ asset('images/' . $question->image) }}" class="img-fluid form-control mt-3"
                                                        style="max-width: 300px;height: 300px;">
                                                    <button type="button" id="remove-image-btn" class="btn btn-danger mt-2">Remove Image</button>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button class="btn btn-primary mr-1" type="submit">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('backend.layouts.partials.setting_sidebar')
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#remove-image-btn').on('click', function(e) {
                e.preventDefault();
                console.log(document.getElementById('remove-image-btn'))

                fetch('{{ route('admin.quiz.question.removeImage', $question->id) }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: 'image'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', data.message)
                            $('#question-image').remove();
                            $('#remove-image-btn').remove();
                        }
                    });

            });

            $('#remove-visual-btn').on('click', function(e) {
                e.preventDefault();
                fetch('{{ route('admin.quiz.question.removeImage', $question->id) }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: 'visual_explanation'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('success', data.message)
                            $('#visual-explanation-image').remove();
                            $('#visual-explanation-video').remove();
                            $('#remove-visual-btn').remove();
                        }
                    });

            });
        });
    </script>
@endpush
