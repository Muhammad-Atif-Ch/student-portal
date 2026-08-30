@php
    $existingBlocks = isset($response) ? $response->blocks : collect();
@endphp

<style>
    /* Reset so "Normal" always renders as normal paragraph text, regardless of
       what tag/size the theme's typography styles would otherwise apply. */
    .note-editable, .note-editable p, .note-editable div, .note-editable li, .note-editable span {
        font-size: 15px !important;
        font-weight: 400 !important;
        line-height: 1.6 !important;
    }
    .note-editable h1 { font-size: 1.6em !important; font-weight: 700 !important; }
    .note-editable h2 { font-size: 1.4em !important; font-weight: 700 !important; }
    .note-editable h3 { font-size: 1.25em !important; font-weight: 700 !important; }
    .note-editable h4 { font-size: 1.1em !important; font-weight: 700 !important; }
    .note-editable h5, .note-editable h6 { font-size: 1em !important; font-weight: 700 !important; }
    .note-editable b, .note-editable strong { font-weight: 700 !important; }
</style>

<div class="form-group">
    <label>Case Study Title <small style="color: red">*</small></label>
    <input type="text" class="form-control" name="title" value="{{ old('title', $response->title ?? '') }}" placeholder="e.g. Scenario 1" required>
</div>

<div class="form-group">
    <label>Type <small style="color: red">*</small></label>
    <select class="form-control" name="cpc_type_id" required>
        <option value="">Select Type</option>
        @foreach ($cpcTypes as $cpcType)
            <option value="{{ $cpcType->id }}" {{ old('cpc_type_id', $response->cpc_type_id ?? '') == $cpcType->id ? 'selected' : '' }}>{{ $cpcType->title }}</option>
        @endforeach
    </select>
</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
    <label class="mb-0">Content Blocks <small style="color: red">*</small></label>
    <div>
        <button type="button" id="add-text-block" class="btn btn-sm btn-outline-primary"><i class="fas fa-align-left"></i> Add Text</button>
        <button type="button" id="add-image-block" class="btn btn-sm btn-outline-primary"><i class="fas fa-image"></i> Add Image</button>
        <button type="button" id="add-list-block" class="btn btn-sm btn-outline-primary"><i class="fas fa-list-ol"></i> Add List</button>
    </div>
</div>

<div id="blocks-container">
    @foreach ($existingBlocks as $block)
        @include('backend.cpc.case-study._block', ['uid' => $block->id, 'type' => $block->type, 'block' => $block])
    @endforeach
</div>

@if ($existingBlocks->isEmpty())
    <p class="text-muted" id="no-blocks-msg">No blocks added yet. Use the buttons above to build the case study content in order.</p>
@endif

<template id="block-template-text">
    @include('backend.cpc.case-study._block', ['uid' => '__UID__', 'type' => 'text', 'block' => null])
</template>
<template id="block-template-image">
    @include('backend.cpc.case-study._block', ['uid' => '__UID__', 'type' => 'image', 'block' => null])
</template>
<template id="block-template-list">
    @include('backend.cpc.case-study._block', ['uid' => '__UID__', 'type' => 'list', 'block' => null])
</template>

@push('scripts')
    <script src="{{ asset('assets/bundles/summernote/summernote-bs4.js') }}"></script>
    <script>
        $(function() {
            var newBlockCounter = 0;
            var summernoteToolbar = [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']],
            ];

            function initEditors($scope) {
                $scope.find('.cs-summernote').summernote({
                    toolbar: summernoteToolbar,
                    height: 180,
                    placeholder: 'Paragraph text',
                });
            }

            initEditors($('#blocks-container'));

            function nextUid() {
                newBlockCounter += 1;
                return 'new_' + newBlockCounter;
            }

            function appendBlock(templateId) {
                var uid = nextUid();
                var html = $(templateId).html().split('__UID__').join(uid);
                $('#no-blocks-msg').remove();
                var $block = $(html).appendTo('#blocks-container');
                initEditors($block);
            }

            $('#add-text-block').on('click', function() {
                appendBlock('#block-template-text');
            });
            $('#add-image-block').on('click', function() {
                appendBlock('#block-template-image');
            });
            $('#add-list-block').on('click', function() {
                appendBlock('#block-template-list');
            });

            $('#blocks-container').on('click', '.remove-block', function() {
                var $block = $(this).closest('.cs-block');
                $block.find('.cs-summernote').summernote('destroy');
                $block.remove();
            });

            $('#blocks-container').on('click', '.move-up', function() {
                var $block = $(this).closest('.cs-block');
                var $prev = $block.prev('.cs-block');
                if ($prev.length) {
                    $block.insertBefore($prev);
                }
            });

            $('#blocks-container').on('click', '.move-down', function() {
                var $block = $(this).closest('.cs-block');
                var $next = $block.next('.cs-block');
                if ($next.length) {
                    $block.insertAfter($next);
                }
            });

            $('#blocks-container').on('change', 'input[type="file"]', function() {
                var input = this;
                var $preview = $(this).closest('.cs-block').find('.image-preview');
                var $img = $preview.find('.preview-img');

                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $img.attr('src', e.target.result);
                        $preview.show();
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });

            $('form').on('submit', function() {
                if ($('#blocks-container .cs-block').length === 0) {
                    alert('Please add at least one content block (text, image or list).');
                    return false;
                }
            });
        });
    </script>
@endpush
