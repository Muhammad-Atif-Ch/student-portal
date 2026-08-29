<div class="form-group">
    <label>Term <small style="color: red">*</small></label>
    <input type="text" name="term" class="form-control" value="{{ old('term', $response->term ?? '') }}" required>
</div>
<div class="form-group">
    <label>Explanation <small style="color: red">*</small></label>
    <textarea name="explanation" class="form-control" rows="5" required>{{ old('explanation', $response->explanation ?? '') }}</textarea>
</div>
<div class="form-group">
    <label>Image</label>
    <input type="file" name="image" class="form-control-file" accept="image/*" id="technicalDictionaryImageInput">
    <div class="mt-2 image-preview" @if (empty($response->image_url ?? null)) style="display:none;" @endif id="technicalDictionaryImagePreviewWrap">
        <img src="{{ $response->image_url ?? '' }}" alt="" class="img-thumbnail preview-img" style="max-width: 260px;" id="technicalDictionaryImagePreview">
        <small class="d-block text-muted mt-1">Choose a new file to replace this image.</small>
    </div>
</div>

<script>
    document.getElementById('technicalDictionaryImageInput').addEventListener('change', function(e) {
        var file = e.target.files[0];
        var $wrap = document.getElementById('technicalDictionaryImagePreviewWrap');
        var $img = document.getElementById('technicalDictionaryImagePreview');

        if (!file) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function(ev) {
            $img.src = ev.target.result;
            $wrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
</script>
