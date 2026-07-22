@extends('backend.layouts.app')
@section('title', __('Edit Glossary Term'))
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <form action="{{ route('admin.translations.glossary.update', $translationGlossary->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="card-header">
                                    <h4>Edit Glossary Term</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Source Term <small style="color: red">*</small></label>
                                                <input type="text" name="source_term" class="form-control" required value="{{ $translationGlossary->source_term }}">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Language <small style="color: red">*</small></label>
                                                <select class="form-control" name="language_id">
                                                    <option value="" selected>Select Option</option>
                                                    @foreach ($languages as $language)
                                                        <option value="{{ $language->id }}" {{ old('language_id', $language->id) == $translationGlossary->language_id ? 'selected' : '' }}>
                                                            {{ $language->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4">
                                            <div class="form-group">
                                                <label>Target Term <small style="color: red">*</small></label>
                                                <input type="text" name="target_term" class="form-control" required value="{{ $translationGlossary->target_term }}">
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
    {{-- <script>
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
    </script> --}}
@endpush
