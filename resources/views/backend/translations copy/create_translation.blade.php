@extends('backend.layouts.app')
@section('title', __('Bulk Question Translation'))
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Question Translation') }}</h4>
                    </div>
                    <div class="card-body">
                        <button type="button" id="startBtnTranslation" class="btn btn-primary">Start Translation</button>
                        <button type="button" id="stopBtnTranslation" class="btn btn-danger ml-2" disabled>Stop Translation</button>

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
