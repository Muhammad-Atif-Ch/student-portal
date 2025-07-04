@extends('backend.layouts.app')
@section('title', __('Text to Speech Conversion'))

@push('css')
    <style>
        .log-entry {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .log-entry:last-child {
            border-bottom: none;
        }

        .log-success {
            color: #28a745;
        }

        .log-error {
            color: #dc3545;
        }

        .log-warning {
            color: #ffc107;
        }

        .log-info {
            color: #17a2b8;
        }

        #logContainer {
            max-height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            {{-- <div class="section-header">
                <h1>Text to Speech Conversion</h1>
            </div> --}}
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Text to Speech Conversion') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="ttsForm" method="POST" action="{{ route('admin.translations.tts.start') }}">
                            @csrf
                            <div class="form-group">
                                <label for="api_key">Azure Speech API Key</label>
                                <input type="text" class="form-control" id="api_key" name="api_key" value="{{ env('AZURE_SPEECH_API_KEY') }}">
                                <small class="form-text text-muted">
                                    Use an Azure Speech Service API Key.
                                    <a href="https://portal.azure.com/#create/Microsoft.CognitiveServicesSpeechServices" target="_blank">Get API Key here</a>
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="region">Azure Region</label>
                                <input type="text" class="form-control" id="region" name="region" value="{{ env('AZURE_SPEECH_API_REGION') }}" required>
                                <small class="form-text text-muted">
                                    The Azure region where your Speech Service is deployed (e.g., eastus, westeurope)
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="batch_size">Batch Size</label>
                                <input type="number" class="form-control" id="batch_size" name="batch_size" value="10" min="1" max="50">
                                <small class="form-text text-muted">
                                    Number of questions to process in parallel (1-50)
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="startVoiceBtn">Start Voice Conversion</button>
                            <button type="button" id="stopVoiceBtn" class="btn btn-danger ml-2" disabled>Stop Voice Conversion</button>
                        </form>

                        <div class="mt-4">
                            <h5>Conversion Logs</h5>
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

@push('scripts')
    <script src="{{ asset('assets/js/broadcast.js') }}"></script>
@endpush
