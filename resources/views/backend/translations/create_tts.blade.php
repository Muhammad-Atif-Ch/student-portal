@extends('backend.layouts.app')
@section('title', __('Text to Speech Conversion'))
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Text to Speech Conversion') }}</h4>
                    </div>
                    <div class="card-body">
                        <button type="button" id="startBtnTts" class="btn btn-primary">Start Voice Conversion</button>
                        <button type="button" id="stopBtnTts" class="btn btn-danger ml-2" disabled>Stop Voice Conversion</button>
                        <button type="button" id="viewReportBtnTts" class="btn btn-outline-secondary ml-2">View Full Report</button>

                        {{-- Live outcome counts, filled in by fetchProgress() while a run is active --}}
                        <div id="ttsBreakdown" class="mt-3"></div>

                        {{-- Per-language completed/partial/errored/skipped breakdown.
                             Populated live during a run, or from the DB-backed report
                             when "View Full Report" is clicked. --}}
                        <div id="ttsReportPanel" class="mt-4">
                            <h5>{{ __('Progress by Language') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Language') }}</th>
                                            <th>{{ __('Completed') }}</th>
                                            <th>{{ __('Partial') }}</th>
                                            <th>{{ __('Errored') }}</th>
                                            <th>{{ __('Skipped') }}</th>
                                            <th>{{ __('Not Started') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="languageBreakdownBody">
                                        <tr>
                                            <td colspan="6" class="text-muted">{{ __('No data yet - start a voice conversion run or view the full report.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="mt-4">{{ __('Recent Errors / Needs Attention') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('When / Status') }}</th>
                                            <th>{{ __('Question') }}</th>
                                            <th>{{ __('Language') }}</th>
                                            <th>{{ __('Details') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentErrorsBody">
                                        <tr>
                                            <td colspan="4" class="text-muted">{{ __('No errors so far.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
