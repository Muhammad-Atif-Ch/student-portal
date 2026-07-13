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
                        <button type="submit" id="startVoiceBtn" class="btn btn-primary">Start Voice Conversion</button>
                        <button type="button" id="stopVoiceBtn" class="btn btn-danger ml-2" disabled>Stop Voice
                            Conversion</button>

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
