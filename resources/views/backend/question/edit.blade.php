@extends('backend.layouts.app')
@section('title', __('Question'))
@section('style')
    <style>
        .swal2-popup {
            background: rgba(245, 245, 245, 0.95) !important;
            backdrop-filter: blur(10px);
            border-radius: 15px !important;
            padding: 2em !important;
        }

        .swal2-title {
            color: #2c3e50 !important;
            font-size: 1.5em !important;
            font-weight: 500 !important;
        }

        .swal2-html-container {
            color: #2c3e50 !important;
        }

        .progress-wrapper {
            padding: 15px;
            margin: 10px 0;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .progress-label {
            color: #34495e;
            font-size: 14px;
        }

        .progress-percentage {
            color: #3498db;
            font-weight: 500;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background: rgba(189, 195, 199, 0.3);
            margin-bottom: 15px;
        }

        .progress-bar {
            background: #3498db;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-message {
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
@endsection
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form id="questionForm" action="{{ route('admin.quiz.question.update', ['quiz' => $quiz_id, 'question' => $question->id]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="update_type" id="update_type">
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
                                                    {{-- <option value="e" @if ($question->correct_answer == 'e') selected @endif>E - Option</option>
                                                    <option value="f" @if ($question->correct_answer == 'f') selected @endif>F - Option</option> --}}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Type <small style="color: red">*</small></label>
                                                <select class="form-control" name="type" required>
                                                    <option value="" selected>Select Option</option>
                                                    <option value="car" @if ($question->type == 'car') selected @endif>Car</option>
                                                    <option value="bike" @if ($question->type == 'bike') selected @endif>Bike</option>
                                                    <option value="both" @if ($question->type == 'both') selected @endif>Both</option>
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
                                    <button class="btn btn-primary mr-1" type="submit" onclick="submitForm('translation')">Translation Update</button>
                                    <button class="btn btn-primary mr-1" type="submit" onclick="submitForm('audio')">Audio File Update</button>
                                    <button class="btn btn-primary mr-1" type="submit" onclick="submitForm('all_data_update')">All Data Update</button>
                                    <button class="btn btn-primary mr-1" type="submit" onclick="submitForm('form_data_update')">Form Data Update</button>
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
        let progressAlert = null;
        let isTranslationActive = false;

        function checkQuestionProgress(questionId) {
            if (!isTranslationActive) return;

            $.ajax({
                url: "{{ route('admin.translations.question.progress', ['question_id' => ':id']) }}".replace(':id', questionId),
                method: 'GET',
                success: function(response) {
                    updateProgressUI(response, questionId);
                },
                error: function(xhr) {
                    console.error('Error checking progress:', xhr);
                }
            });
        }

        function updateProgressUI(data, questionId) {
            const {
                progress,
                percentage
            } = data;
            const {
                status,
                message
            } = progress;

            // Create progress HTML with improved styling
            const progressHtml = `
            <div class="progress-wrapper">
                <div class="progress-info">
                    <div class="progress-label">
                        <span>Translation Progress</span>
                    </div>
                    <div class="progress-percentage">
                        <span>${percentage}%</span>
                    </div>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" 
                        aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100" 
                        style="width: ${percentage}%;">
                    </div>
                </div>
                <div class="progress-message">
                    ${message || 'Processing...'}
                </div>
            </div>
        `;

            // Show or update progress alert with improved styling
            if (!progressAlert) {
                progressAlert = Swal.fire({
                    title: 'Translating Question',
                    html: progressHtml,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showCloseButton: false,
                    position: 'center',
                    width: '32rem',
                    backdrop: `rgba(44, 62, 80, 0.3)`,
                    customClass: {
                        popup: 'swal2-show'
                    }
                });
            } else {
                Swal.update({
                    html: progressHtml
                });
            }

            // Handle completion states with improved styling
            if (["completed", "stopped", "error"].includes(status)) {
                isTranslationActive = false;
                setTimeout(() => {
                    if (progressAlert) {
                        progressAlert.close();
                        progressAlert = null;
                    }

                    // Show completion message with improved styling
                    let title = "Translation Complete";
                    let icon = "success";
                    let confirmButtonColor = '#2ecc71';

                    if (status === "error") {
                        title = "Translation Failed";
                        icon = "error";
                        confirmButtonColor = '#e74c3c';
                    } else if (status === "stopped") {
                        title = "Translation Stopped";
                        icon = "warning";
                        confirmButtonColor = '#f1c40f';
                    }

                    Swal.fire({
                        title: title,
                        text: message || 'Process completed',
                        icon: icon,
                        confirmButtonText: 'OK',
                        confirmButtonColor: confirmButtonColor,
                        backdrop: `rgba(245, 245, 245, 0.95)`,
                        customClass: {
                            popup: 'swal2-show'
                        }
                    }).then(() => {
                        window.location.href = "{{ route('admin.quiz.question.index', ['quiz' => $quiz_id]) }}";
                    });
                }, 1000);
            } else {
                // Continue checking progress
                setTimeout(() => checkQuestionProgress(questionId), 1000);
            }
        }

        // Handle form submission
        $(document).ready(function() {
            $('#questionForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const form = $(this);
                const formData = new FormData(this);
                const submitButton = form.find('button[type="submit"]');
                console.log('Submitting form with update_type:', formData);
                // Disable submit button
                submitButton.prop('disabled', true);

                // Show loading state
                Swal.fire({
                    title: 'Updating Question',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Perform AJAX request
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST', // Force POST method
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Response:', response);
                        Swal.close();

                        if (response.data && response.data.question_id) {
                            isTranslationActive = true;
                            checkQuestionProgress(response.data.question_id);
                        } else {
                            Swal.fire({
                                title: 'Success',
                                text: response.message || 'Question updated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "{{ route('admin.quiz.question.index', ['quiz' => $quiz_id]) }}";
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        submitButton.prop('disabled', false);
                        Swal.close();

                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'An error occurred while updating the question.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });

                return false;
            });
        });


        $(document).ready(function() {
            $('#remove-image-btn').on('click', function(e) {
                e.preventDefault();
                console.log(document.getElementById('remove-image-btn'))

                fetch('{{ route('admin.question.removeImage', $question->id) }}', {
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
                fetch('{{ route('admin.question.removeImage', $question->id) }}', {
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

        function submitForm(type) {
            document.getElementById('update_type').value = type;
        }
    </script>
@endpush
