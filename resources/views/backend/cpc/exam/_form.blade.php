<div class="form-group">
    <label>CPC Type <small style="color: red">*</small></label>
    <select name="cpc_type_id" class="form-control" required>
        <option value="">Select Type</option>
        @foreach ($cpcTypes as $cpcType)
            <option value="{{ $cpcType->id }}" {{ old('cpc_type_id', $response->cpc_type_id ?? '') == $cpcType->id ? 'selected' : '' }}>
                {{ $cpcType->title }}
            </option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Title <small style="color: red">*</small></label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $response->title ?? '') }}" required>
</div>
<div class="form-group">
    <label>Mode <small style="color: red">*</small></label>
    <select name="mode" class="form-control" required>
        <option value="full" {{ old('mode', $response->mode ?? '') == 'full' ? 'selected' : '' }}>Full Exam</option>
        <option value="short" {{ old('mode', $response->mode ?? '') == 'short' ? 'selected' : '' }}>Short Exam</option>
    </select>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Total Time (minutes) <small style="color: red">*</small></label>
            <input type="number" min="1" name="total_time_minutes" class="form-control" value="{{ old('total_time_minutes', $response->total_time_minutes ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Total Questions <small style="color: red">*</small></label>
            <input type="number" min="1" name="total_questions" class="form-control" value="{{ old('total_questions', $response->total_questions ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Passing Score <small style="color: red">*</small></label>
            <input type="number" min="1" name="passing_score" class="form-control" value="{{ old('passing_score', $response->passing_score ?? '') }}" required>
        </div>
    </div>
</div>
<div class="form-group">
    <label>Minimum Marks Required Per Scenario (Case Study)</label>
    <input type="number" min="0" name="min_marks_per_scenario" class="form-control" value="{{ old('min_marks_per_scenario', $response->min_marks_per_scenario ?? '') }}">
    <small class="form-text text-muted">Leave empty if this exam mode has no per-scenario compulsory minimum. If set, a learner scoring below this in any case study scenario fails the exam, regardless of overall score.</small>
</div>
<div class="form-group form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $response->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Active</label>
</div>
