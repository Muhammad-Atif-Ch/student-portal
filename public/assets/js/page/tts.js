/**
 * Text to Speech conversion handling
 */
"use strict";

function initTTSProgress() {
    let progressAlert = null;
    let intervalId = null;
    let isConversionActive = false;
    let lastProgressUpdate = 0;

    // Wait for broadcast to be available
    if (typeof TranslationBroadcast === "undefined") {
        console.error("TranslationBroadcast not loaded");
        return;
    }

    // Setup event listeners
    setupEventListeners();
    checkExistingProgress();

    function setupEventListeners() {
        // Listen for broadcast events
        window.addEventListener("tts_progress_update", (event) => {
            if (!event.detail) {
                console.warn("Invalid progress update event:", event);
                return;
            }
            // Prevent duplicate updates within 500ms
            const now = Date.now();
            if (now - lastProgressUpdate < 500) {
                return;
            }
            lastProgressUpdate = now;
            updateProgressUI(event.detail);
        });

        window.addEventListener("tts_complete", (event) => {
            isConversionActive = false;
            if (progressAlert) {
                progressAlert.close();
                progressAlert = null;
            }
            stopProgressPolling();
        });

        // Setup form handlers
        const form = document.getElementById("ttsForm");
        if (form) {
            setupTTSForm(form);
            // Check and restore button states
            restoreButtonStates();
        }
    }

    function restoreButtonStates() {
        const startBtn = document.getElementById("startVoiceBtn");
        const stopBtn = document.getElementById("stopVoiceBtn");
        if (!startBtn || !stopBtn) return;

        // Check if conversion is in progress
        const inProgress = localStorage.getItem("ttsInProgress") === "true";
        const progressData = localStorage.getItem("ttsProgress");

        if (inProgress && progressData) {
            try {
                const data = JSON.parse(progressData);
                const status = data.progress?.status || data.status;

                if (status === "running") {
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
                    isConversionActive = true;
                    startProgressPolling();
                } else {
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                    isConversionActive = false;
                }
            } catch (e) {
                console.warn("Failed to parse progress data:", e);
                resetButtonStates();
            }
        } else {
            resetButtonStates();
        }
    }

    function resetButtonStates() {
        const startBtn = document.getElementById("startVoiceBtn");
        const stopBtn = document.getElementById("stopVoiceBtn");
        if (startBtn && stopBtn) {
            startBtn.disabled = false;
            stopBtn.disabled = true;
            isConversionActive = false;
        }
    }

    function startProgressPolling() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        isConversionActive = true;
        fetchProgress();
        intervalId = setInterval(fetchProgress, 5000);
    }

    function stopProgressPolling() {
        isConversionActive = false;
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        localStorage.removeItem("ttsInProgress");
        localStorage.removeItem("ttsProgress");
        resetButtonStates();
    }

    async function fetchProgress() {
        if (!isConversionActive) return;

        try {
            const response = await fetch(routes.tts.progress, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.error) {
                stopProgressPolling();
                if (progressAlert) {
                    progressAlert.close();
                    progressAlert = null;
                }
                addLog("Error in conversion process: " + data.error, "error");
                Swal.fire({
                    title: "Error",
                    text:
                        "Conversion process encountered an error: " +
                        data.error,
                    icon: "error",
                    position: "bottom-end",
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    timerProgressBar: true,
                });
            } else {
                updateProgressUI(data);
            }
        } catch (error) {
            console.error("Failed to fetch progress:", error);
            stopProgressPolling();
            addLog(
                "Failed to fetch conversion progress: " + error.message,
                "error"
            );
            Swal.fire({
                title: "Error",
                text: "Failed to fetch conversion progress",
                icon: "error",
                position: "bottom-end",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                timerProgressBar: true,
            });
        }
    }

    function updateProgressUI(data) {
        if (!data) return;

        // Handle both data structures (direct and nested)
        const percent = parseFloat(data.percentage || data.percent || 0);
        const progress = data.progress || data;
        const message = progress.message || "Processing...";
        const status = progress.status || "running";
        const completed = parseInt(progress.completed || 0);
        const total = parseInt(progress.total || 0);

        // Validate progress data
        if (isNaN(percent) || isNaN(completed) || isNaN(total)) {
            console.warn("Invalid progress data values:", {
                percent,
                completed,
                total,
            });
            return;
        }

        // Only show and store progress if status is running
        if (status === "running") {
            isConversionActive = true;
            // Update localStorage
            localStorage.setItem(
                "ttsProgress",
                JSON.stringify({
                    percentage: percent,
                    progress: {
                        completed,
                        total,
                        message,
                        status,
                    },
                })
            );

            // Update button states
            const startBtn = document.getElementById("startVoiceBtn");
            const stopBtn = document.getElementById("stopVoiceBtn");
            if (startBtn && stopBtn) {
                startBtn.disabled = true;
                stopBtn.disabled = false;
            }

            // Create or update progress alert
            requestAnimationFrame(() => {
                const progressHtml = `
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: ${percent}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">${completed} / ${total}</small>
                        <small class="text-primary font-weight-bold">${Math.round(
                            percent
                        )}%</small>
                    </div>
                    <div class="text-muted" style="font-size: 13px;">${message}</div>
                `;

                if (!progressAlert) {
                    progressAlert = Swal.fire({
                        title: '<small class="text-primary">Voice Conversion Progress</small>',
                        html: progressHtml,
                        position: "bottom-end",
                        showConfirmButton: false,
                        showCloseButton: true,
                        backdrop: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        width: "280px",
                        padding: "0.75rem",
                        customClass: {
                            popup: "tts-progress-popup shadow-sm",
                            title: "tts-progress-title",
                            closeButton: "tts-progress-close",
                            container: "tts-progress-container",
                        },
                        didOpen: (toast) => {
                            const popup = Swal.getPopup();
                            popup.style.position = "fixed";
                            popup.style.bottom = "20px";
                            popup.style.right = "20px";
                            popup.style.margin = "0";
                            popup.style.border = "none";
                            popup.style.borderRadius = "8px";
                            popup.style.background = "#fff";
                            popup.style.boxShadow = "0 0 15px rgba(0,0,0,0.1)";

                            // Style the title
                            const title = popup.querySelector(".swal2-title");
                            if (title) {
                                title.style.fontSize = "14px";
                                title.style.padding = "0";
                                title.style.marginBottom = "10px";
                            }

                            // Style the close button
                            const closeButton =
                                popup.querySelector(".swal2-close");
                            if (closeButton) {
                                closeButton.style.fontSize = "20px";
                                closeButton.style.padding = "0";
                                closeButton.style.marginRight = "5px";
                                closeButton.style.marginTop = "2px";
                            }

                            // Add custom styles to container
                            const container =
                                document.querySelector(".swal2-container");
                            if (container) {
                                container.style.position = "fixed";
                                container.style.padding = "0";
                                container.style.background = "none";
                            }
                        },
                        willClose: () => {
                            // Only allow closing if conversion is not active
                            if (isConversionActive) {
                                return false;
                            }
                            progressAlert = null;
                        },
                    });
                } else {
                    Swal.update({
                        html: progressHtml,
                    });
                }
            });
        }

        // Handle completion states
        if (["completed", "stopped", "error"].includes(status)) {
            isConversionActive = false;
            setTimeout(() => {
                if (progressAlert) {
                    progressAlert.close();
                    progressAlert = null;
                }
                localStorage.removeItem("ttsProgress");
                localStorage.removeItem("ttsInProgress");
                TranslationBroadcast.sendMessage("tts_complete", { status });

                // Reset buttons
                const startBtn = document.getElementById("startVoiceBtn");
                const stopBtn = document.getElementById("stopVoiceBtn");
                if (startBtn && stopBtn) {
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                }

                // Show completion message
                let completionMessage = "";
                let alertType = "success";

                if (status === "completed") {
                    completionMessage =
                        "Voice conversion completed successfully!";
                } else if (status === "stopped") {
                    completionMessage = "Voice conversion was stopped by user.";
                    alertType = "warning";
                } else if (status === "error") {
                    completionMessage =
                        "Voice conversion encountered an error.";
                    alertType = "error";
                }

                // Add to logs
                addLog(completionMessage, alertType);

                // Show completion alert
                Swal.fire({
                    title:
                        status === "completed"
                            ? "Success!"
                            : status === "stopped"
                            ? "Stopped"
                            : "Error",
                    text: completionMessage,
                    icon: alertType,
                    position: "bottom-end",
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    timerProgressBar: true,
                    customClass: {
                        popup: "tts-complete-popup shadow-sm",
                    },
                    didOpen: (toast) => {
                        toast.style.marginBottom = "20px";
                        toast.style.marginRight = "20px";
                    },
                });
            }, 1000);
        }
    }

    function setupTTSForm(form) {
        const startBtn = document.getElementById("startVoiceBtn");
        const stopBtn = document.getElementById("stopVoiceBtn");
        const logsDiv = document.getElementById("logs");
        const clearLogsBtn = document.getElementById("clearLogs");
        let isSubmitting = false;

        // Form submit handler
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            if (isSubmitting || isConversionActive) return;
            isSubmitting = true;

            // Close any existing alert
            if (progressAlert) {
                progressAlert.close();
                progressAlert = null;
            }

            startBtn.disabled = true;
            stopBtn.disabled = false;
            startBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Starting...';

            try {
                const response = await fetch(routes.tts.start, {
                    method: "POST",
                    body: new FormData(form),
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'input[name="_token"]'
                        ).value,
                    },
                });

                const data = await response.json();
                if (data.success) {
                    addLog("Voice conversion process started", "success");
                    localStorage.setItem("ttsInProgress", "true");
                    isConversionActive = true;
                    startProgressPolling();
                } else {
                    addLog(
                        `Error: ${
                            data.message || "Failed to start conversion"
                        }`,
                        "error"
                    );
                    resetButtonStates();
                }
            } catch (error) {
                addLog("Network error occurred", "error");
                resetButtonStates();
            } finally {
                isSubmitting = false;
                startBtn.innerHTML = "Start Voice Conversion";
            }
        });

        // Stop button handler
        let isStoppingConversion = false;
        stopBtn.addEventListener("click", async () => {
            if (isStoppingConversion || !isConversionActive) return;
            isStoppingConversion = true;

            stopBtn.disabled = true;
            stopBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Stopping...';

            try {
                const response = await fetch(routes.tts.stop, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'input[name="_token"]'
                        ).value,
                    },
                });
                const data = await response.json();
                if (data.success) {
                    addLog("Voice conversion stopped", "warning");
                    stopProgressPolling();
                    if (progressAlert) {
                        progressAlert.close();
                        progressAlert = null;
                    }
                } else {
                    addLog("Failed to stop conversion", "error");
                    stopBtn.disabled = false;
                }
            } catch (error) {
                addLog("Failed to stop conversion", "error");
                stopBtn.disabled = false;
            } finally {
                isStoppingConversion = false;
                stopBtn.innerHTML = "Stop Voice Conversion";
            }
        });

        // Clear logs button handler
        clearLogsBtn?.addEventListener("click", () => {
            logsDiv.innerHTML = "";
            addLog("Logs cleared");
        });
    }

    // Optimize log updates
    const logQueue = [];
    let isProcessingLogs = false;

    function processLogQueue() {
        if (!isProcessingLogs && logQueue.length > 0) {
            isProcessingLogs = true;
            requestAnimationFrame(() => {
                const logsDiv = document.getElementById("logs");
                if (logsDiv) {
                    // Process all queued logs at once
                    const fragment = document.createDocumentFragment();
                    while (logQueue.length > 0) {
                        const { message, type } = logQueue.shift();
                        const timestamp = new Date().toLocaleTimeString();
                        const logEntry = document.createElement("div");
                        logEntry.className = `log-entry log-${type}`;
                        logEntry.innerHTML = `<span class="text-muted">[${timestamp}]</span> ${message}`;
                        fragment.appendChild(logEntry);
                    }
                    logsDiv.appendChild(fragment);
                    logsDiv.scrollTop = logsDiv.scrollHeight;
                }
                isProcessingLogs = false;
                if (logQueue.length > 0) {
                    processLogQueue();
                }
            });
        }
    }

    function addLog(message, type = "info") {
        logQueue.push({ message, type });
        processLogQueue();
    }

    function checkExistingProgress() {
        const inProgress = localStorage.getItem("ttsInProgress");
        if (inProgress === "true") {
            isConversionActive = true;
            startProgressPolling();
            restoreButtonStates();
        }
    }
}

// Initialize TTS progress handler
document.addEventListener("DOMContentLoaded", initTTSProgress);
