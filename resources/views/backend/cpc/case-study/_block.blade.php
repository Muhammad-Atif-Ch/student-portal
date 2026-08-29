@php
    $items = $block->items ?? [];
    $listStyle = $block->list_style ?? 'bullet';
    $labels = ['text' => 'Text Block', 'image' => 'Image Block', 'list' => 'List Block'];
    $icons = ['text' => 'align-left', 'image' => 'image', 'list' => 'list-ol'];
@endphp
<div class="card cs-block mb-3" data-type="{{ $type }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-{{ $icons[$type] }}"></i> {{ $labels[$type] }}</strong>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary move-up" title="Move up"><i class="fas fa-arrow-up"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary move-down" title="Move down"><i class="fas fa-arrow-down"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger remove-block" title="Remove"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    <div class="card-body">
        @if ($block)
            <input type="hidden" name="blocks[{{ $uid }}][id]" value="{{ $block->id }}">
        @endif
        <input type="hidden" name="blocks[{{ $uid }}][type]" value="{{ $type }}">

        @if ($type === 'text')
            <textarea class="form-control cs-summernote" name="blocks[{{ $uid }}][content]" placeholder="Paragraph text">{{ $block->content ?? '' }}</textarea>
        @elseif ($type === 'image')
            <input type="file" class="form-control-file" name="blocks[{{ $uid }}][image]" accept="image/*">
            <div class="mt-2 image-preview" @if(! ($block && $block->file_path)) style="display:none;" @endif>
                <img src="{{ $block->file_url ?? '' }}" alt="" class="img-thumbnail preview-img" style="max-width: 260px;">
                <small class="d-block text-muted mt-1">Choose a new file to replace this image.</small>
            </div>
        @elseif ($type === 'list')
            <div class="form-group">
                <label class="small">List Style</label>
                <select class="form-control form-control-sm" name="blocks[{{ $uid }}][list_style]" style="max-width: 200px;">
                    <option value="bullet" {{ $listStyle === 'bullet' ? 'selected' : '' }}>Bullet</option>
                    <option value="numbered" {{ $listStyle === 'numbered' ? 'selected' : '' }}>Numbered</option>
                </select>
            </div>
            <label class="small">List Items <span class="text-muted">(one item per line)</span></label>
            <textarea class="form-control" name="blocks[{{ $uid }}][items_text]" rows="8" style="min-height: 180px;" placeholder="Brakes&#10;Lights &amp; Indicators&#10;Tyres &amp; Wheel Securing Nuts and Markers">{{ implode("\n", $items) }}</textarea>
        @endif
    </div>
</div>
