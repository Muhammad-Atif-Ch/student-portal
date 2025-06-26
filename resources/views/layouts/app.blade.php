<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            // Translation Progress Handler
            document.addEventListener('DOMContentLoaded', function() {
                let progressAlert = null;
                let intervalId = null;
                const broadcastChannel = new BroadcastChannel('translation_progress');

                function showProgressAlert(data) {
                    const percent = data.percentage;
                    const progress = data.progress;
                    const message = progress.message || 'Processing...';
                    const status = progress.status;
                    const completed = progress.completed;
                    const total = progress.total;

                    // Only show and store progress if status is running
                    if (status === 'running') {
                        localStorage.setItem('translationProgress', JSON.stringify({
                            percent,
                            message,
                            status,
                            completed,
                            total,
                            lastUpdate: new Date().getTime()
                        }));

                        // Broadcast to other tabs
                        broadcastChannel.postMessage({
                            type: 'progress_update',
                            data: data
                        });

                        if (!progressAlert) {
                            progressAlert = Swal.fire({
                                title: 'Translation Progress',
                                html: `
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${percent}%">${percent}%</div>
                                    </div>
                                    <div class="mt-2">${message}</div>
                                    <div class="mt-1 text-muted">${completed} / ${total}</div>
                                `,
                                position: 'top-end',
                                showConfirmButton: false,
                                showCloseButton: true,
                                allowOutsideClick: true,
                                allowEscapeKey: true,
                                backdrop: false,
                                width: '300px',
                                padding: '1em',
                                customClass: {
                                    popup: 'translation-progress-popup',
                                    container: 'translation-progress-container'
                                },
                                didClose: () => {
                                    progressAlert = null;
                                }
                            });
                        } else {
                            Swal.update({
                                html: `
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${percent}%">${percent}%</div>
                                    </div>
                                    <div class="mt-2">${message}</div>
                                    <div class="mt-1 text-muted">${completed} / ${total}</div>
                                `
                            });
                        }
                    }

                    if (status === 'completed' || status === 'stopped' || status === 'error') {
                        setTimeout(() => {
                            if (progressAlert) {
                                progressAlert.close();
                                progressAlert = null;
                            }
                            localStorage.removeItem('translationProgress');
                            // Broadcast completion to other tabs
                            broadcastChannel.postMessage({
                                type: 'translation_complete',
                                status: status
                            });
                        }, 3000);
                    }
                }

                function startProgressPolling() {
                    if (!intervalId) {
                        intervalId = setInterval(fetchProgress, 2000);
                    }
                }

                function stopProgressPolling() {
                    if (intervalId) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                }

                async function fetchProgress() {
                    try {
                        const response = await fetch("{{ route('admin.translation.progress') }}");
                        const data = await response.json();

                        if (data.error) {
                            if (progressAlert) {
                                progressAlert.close();
                                progressAlert = null;
                            }
                            stopProgressPolling();
                        } else {
                            showProgressAlert(data);
                        }
                    } catch (error) {
                        console.error('Failed to fetch progress');
                        stopProgressPolling();
                    }
                }

                // Listen for messages from other tabs
                broadcastChannel.onmessage = (event) => {
                    if (event.data.type === 'progress_update') {
                        showProgressAlert(event.data.data);
                    } else if (event.data.type === 'translation_complete') {
                        if (progressAlert) {
                            progressAlert.close();
                            progressAlert = null;
                        }
                        stopProgressPolling();
                    }
                };

                // Check for existing progress on page load
                const savedProgress = localStorage.getItem('translationProgress');
                if (savedProgress) {
                    const progress = JSON.parse(savedProgress);
                    const lastUpdate = progress.lastUpdate;
                    const now = new Date().getTime();

                    // If last update was less than 5 minutes ago
                    if (now - lastUpdate < 300000) {
                        startProgressPolling();
                    } else {
                        localStorage.removeItem('translationProgress');
                    }
                }
            });
        </script>

        <style>
            .translation-progress-popup {
                position: fixed !important;
                top: 20px !important;
                right: 20px !important;
                margin: 0 !important;
                z-index: 9999 !important;
                cursor: move !important;
            }

            .translation-progress-container {
                z-index: 9999 !important;
            }

            .translation-progress-popup .swal2-header {
                cursor: move !important;
            }

            .swal2-container.swal2-backdrop-show {
                background: none !important;
            }

            .swal2-container {
                z-index: 9999 !important;
            }
        </style>
    </body>

</html>
