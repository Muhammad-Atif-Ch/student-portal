<div class="form-group">
    <label>Title <small style="color: red">*</small></label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $response->title ?? '') }}" required>
</div>
