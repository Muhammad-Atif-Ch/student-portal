@extends('backend.layouts.app')
@section('title', __('Bulk Question Translation'))
@section('content')
    <div class="main-content">
        <section class="section">
            {{-- <div class="section-header">
                <h1>Bulk Question Translation</h1>
            </div> --}}
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Question Translation') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="translationForm" method="POST" action="{{ route('admin.translations.start') }}">
                            @csrf
                            <div class="form-group">
                                <label for="api_key">Google Translate API Key</label>
                                <input type="text" class="form-control" id="api_key" name="api_key" value="{{ env('GOOGLE_TRANSLATE_API_KEY') }}" required>
                                <small class="form-text text-muted">
                                    Use a Google Cloud API Key (not OAuth token).
                                    <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Get API Key here</a>
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="source_language">Source Language Code</label>
                                <input type="text" class="form-control" id="source_language" name="source_language" value="en" required>
                            </div>
                            <div class="form-group">
                                <label for="batch_size">Batch Size</label>
                                <input type="number" class="form-control" id="batch_size" name="batch_size" value="10" min="1" max="50">
                            </div>
                            <button type="submit" class="btn btn-primary" id="startBtn">Start Translation</button>
                            <button type="button" id="stopBtn" class="btn btn-danger ml-2" disabled>Stop Translation</button>
                        </form>

                        <div class="mt-4">
                            <h5>Translation Logs</h5>
                            <div id="logContainer" class="border p-3">
                                <div id="logs"></div>
                            </div>
                            <button type="button" id="clearLogs" class="btn btn-sm btn-secondary mt-2">Clear Logs</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
