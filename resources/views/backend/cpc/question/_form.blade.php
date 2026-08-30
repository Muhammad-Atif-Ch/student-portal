@php
    $optionLabels = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];
    $existingOptions = isset($response) ? $response->options->keyBy('option_key') : collect();
    $correctOption = old('correct_option', optional($existingOptions->firstWhere('is_correct', true))->option_key);
@endphp


<div class="form-group">
    <label>Case Study</label>
    <select name="cpc_case_study_id" class="form-control">
        <option value="">None</option>
        @foreach ($caseStudies as $caseStudy)
            <option value="{{ $caseStudy->id }}" {{ old('cpc_case_study_id', $response->cpc_case_study_id ?? '') == $caseStudy->id ? 'selected' : '' }}>{{ $caseStudy->title }} - {{ ucfirst($caseStudy->type->title ?? '-') }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Question <small style="color: red">*</small></label>
    <textarea name="question" class="form-control" rows="3" required>{{ old('question', $response->question ?? '') }}</textarea>
</div>

<div class="row">
    @foreach ($optionLabels as $key => $label)
        @php $existing = $existingOptions->get($key); @endphp
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card" style="border:1px solid #e0e0e0;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Option {{ $label }}</strong>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_option" id="correct_{{ $key }}" value="{{ $key }}" {{ $correctOption == $key ? 'checked' : '' }} required>
                            <label class="form-check-label" for="correct_{{ $key }}">Correct</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <select class="form-control option-type" name="options[{{ $key }}][type]" data-key="{{ $key }}">
                            <option value="text" {{ old("options.$key.type", $existing->type ?? 'text') == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="file" {{ old("options.$key.type", $existing->type ?? '') == 'file' ? 'selected' : '' }}>File</option>
                        </select>
                    </div>

                    <div class="form-group option-text-field" id="text-field-{{ $key }}">
                        <input type="text" class="form-control" name="options[{{ $key }}][text_value]" value="{{ old("options.$key.text_value", $existing->text_value ?? '') }}" placeholder="Option {{ $label }} text">
                    </div>

                    <div class="form-group option-file-field" id="file-field-{{ $key }}" style="display:none;">
                        <input type="file" class="form-control" name="options[{{ $key }}][file]" accept="image/*,video/*,audio/*">
                        @if ($existing && $existing->file_path)
                            <small class="d-block mt-1">
                                Current file: <a href="{{ $existing->file_url }}" target="_blank">{{ $existing->file_path }}</a>
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="form-group mt-3">
    <label>Answer Explanation</label>
    <textarea name="answer_explanation" class="form-control" rows="3">{{ old('answer_explanation', $response->answer_explanation ?? '') }}</textarea>
</div>

@push('scripts')
    <script>
        $(function() {
            function toggleOptionField(key) {
                var type = $('select[name="options[' + key + '][type]"]').val();
                if (type === 'file') {
                    $('#text-field-' + key).hide();
                    $('#file-field-' + key).show();
                } else {
                    $('#file-field-' + key).hide();
                    $('#text-field-' + key).show();
                }
            }

            $('.option-type').each(function() {
                toggleOptionField($(this).data('key'));
            });

            $('.option-type').on('change', function() {
                toggleOptionField($(this).data('key'));
            });
        });
    </script>
@endpush
