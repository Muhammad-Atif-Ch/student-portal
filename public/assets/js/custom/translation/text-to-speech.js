/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$(function () {
    initTtsProgress();
});

function initTtsProgress() {
    if (window._ttsProgressInitialized) {
        return;
    }

    const startBtn = document.getElementById("startBtnTts");
    const stopBtn = document.getElementById("stopBtnTts");
    const reportBtn = document.getElementById("viewReportBtnTts");

    if (!startBtn || !stopBtn) {
        return;
    }

    if (typeof routes === "undefined" || !routes.tts) {
        console.error("routes.tts is not defined");
        return;
    }

    window._ttsProgressInitialized = true;

    let progressAlert = null;
    let intervalId = null;
    let isTtsActive = false;
    let isSubmitting = false;
    let isStopping = false;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    const hasBroadcast = typeof TranslationBroadcast !== "undefined";
    if (!hasBroadcast) {
        console.warn(
            "TranslationBroadcast not loaded - cross-tab sync disabled",
        );
    }

    setupEventListeners();
    checkExistingProgress();

    function setupEventListeners() {
        startBtn.addEventListener("click", handleStart);
        stopBtn.addEventListener("click", handleStop);
        reportBtn?.addEventListener("click", handleViewReport);

        if (hasBroadcast) {
            window.addEventListener("tts_progress_update", (event) => {
                if (!event.detail) return;
                updateProgressUI(event.detail);
            });

            window.addEventListener("tts_complete", () => {
                isTtsActive = false;
                closeProgressAlert();
                stopProgressPolling();
            });
        }

        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible" && isTtsActive) {
                startProgressPolling(true);
            }
        });

        window.addEventListener("beforeunload", () => {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        });

        document.getElementById("clearLogs")?.addEventListener("click", () => {
            const logsDiv = document.getElementById("logs");
            if (logsDiv) logsDiv.innerHTML = "";
            addLog("Logs cleared");
        });
    }

    async function handleStart() {
        if (isSubmitting || isTtsActive) return;
        isSubmitting = true;

        closeProgressAlert();

        startBtn.disabled = true;
        stopBtn.disabled = false;
        const originalStartHtml = startBtn.innerHTML;
        startBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Starting...';

        showProgressAlert({
            percent: 0,
            processed: 0,
            total: 0,
            message: "Initializing voice conversion process...",
        });

        try {
            const response = await fetch(routes.tts.start, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (data.success) {
                addLog("Voice conversion process started", "success");
                localStorage.setItem("ttsInProgress", "true");
                isTtsActive = true;
                startProgressPolling(false);
            } else {
                addLog(
                    `Error: ${data.error || data.message || "Failed to start voice conversion"}`,
                    "error",
                );
                resetButtonStates();
                closeProgressAlert();
            }
        } catch (error) {
            addLog("Network error occurred: " + error.message, "error");
            resetButtonStates();
            closeProgressAlert();
        } finally {
            isSubmitting = false;
            startBtn.innerHTML = originalStartHtml;
        }
    }

    async function handleStop() {
        if (isStopping || !isTtsActive) return;
        isStopping = true;

        stopBtn.disabled = true;
        const originalStopHtml = stopBtn.innerHTML;
        stopBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Stopping...';

        try {
            const response = await fetch(routes.tts.stop, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (data.success) {
                addLog("Voice conversion stopped", "warning");
                if (hasBroadcast) {
                    TranslationBroadcast.sendMessage("tts_complete", {
                        status: "stopped",
                    });
                }
                stopProgressPolling();
                closeProgressAlert();
            } else {
                addLog("Failed to stop voice conversion", "error");
                stopBtn.disabled = false;
            }
        } catch (error) {
            addLog("Failed to stop voice conversion: " + error.message, "error");
            stopBtn.disabled = false;
        } finally {
            isStopping = false;
            stopBtn.innerHTML = originalStopHtml;
        }
    }

    async function handleViewReport() {
        const originalHtml = reportBtn.innerHTML;
        reportBtn.disabled = true;
        reportBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

        try {
            const response = await fetch(routes.tts.report, {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            renderLanguageBreakdown(data.by_language, { fromReport: true });
            renderNeedsAttention(data.needs_attention?.data || []);

            document
                .getElementById("ttsReportPanel")
                ?.classList.remove("d-none");
        } catch (error) {
            console.error("Failed to fetch report:", error);
            notify("error", "Error", "Failed to load the voice conversion report");
        } finally {
            reportBtn.disabled = false;
            reportBtn.innerHTML = originalHtml;
        }
    }

    function startProgressPolling(immediate) {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        isTtsActive = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;

        if (immediate) {
            fetchProgress();
        }
        intervalId = setInterval(fetchProgress, 5000);
    }

    function stopProgressPolling() {
        isTtsActive = false;
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        localStorage.removeItem("ttsInProgress");
        localStorage.removeItem("ttsProgress");
        resetButtonStates();
    }

    function checkExistingProgress() {
        const inProgress = localStorage.getItem("ttsInProgress");
        if (inProgress !== "true") {
            return;
        }
        fetchProgress(true);
    }

    async function fetchProgress(isInitialCheck = false) {
        try {
            const response = await fetch(routes.tts.progress, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.error) {
                stopProgressPolling();
                closeProgressAlert();
                addLog("Error in voice conversion process: " + data.error, "error");
                notify(
                    "error",
                    "Error",
                    "Voice conversion encountered an error: " + data.error,
                );
                return;
            }

            const status = data.progress?.status || "idle";

            if (status === "running") {
                startProgressPolling(false);
                updateProgressUI(data);
            } else if (isInitialCheck) {
                localStorage.removeItem("ttsInProgress");
                localStorage.removeItem("ttsProgress");
                resetButtonStates();
            } else {
                updateProgressUI(data);
            }
        } catch (error) {
            console.error("Failed to fetch progress:", error);
            stopProgressPolling();
            addLog(
                "Failed to fetch voice conversion progress: " + error.message,
                "error",
            );
            notify("error", "Error", "Failed to fetch voice conversion progress");
        }
    }

    function updateProgressUI(data) {
        if (!data) return;

        const percent = parseFloat(data.percentage ?? 0);
        const progress = data.progress || data;
        const message = progress.message || "Processing...";
        const status = progress.status || "running";

        const total = parseInt(progress.total || 0);
        const processed = parseInt(
            progress.processed ?? progress.completed ?? 0,
        );
        const completed = parseInt(progress.completed || 0);
        const partial = parseInt(progress.partial || 0);
        const errored = parseInt(progress.errored || 0);
        const skipped = parseInt(progress.skipped || 0);

        if (isNaN(percent) || isNaN(processed) || isNaN(total)) {
            console.warn("Invalid progress data values:", {
                percent,
                processed,
                total,
            });
            return;
        }

        renderBreakdown({ completed, partial, errored, skipped, total });
        renderLanguageBreakdown(progress.by_language);
        renderRecentErrors(progress.recent_errors);

        if (status === "running") {
            isTtsActive = true;
            localStorage.setItem(
                "ttsProgress",
                JSON.stringify({
                    percentage: percent,
                    progress: { processed, total, message, status },
                }),
            );
            localStorage.setItem("ttsInProgress", "true");

            startBtn.disabled = true;
            stopBtn.disabled = false;

            showProgressAlert({ percent, processed, total, message });
        }

        if (["completed", "stopped", "error"].includes(status)) {
            isTtsActive = false;
            setTimeout(() => {
                closeProgressAlert();
                stopProgressPolling();

                if (hasBroadcast) {
                    TranslationBroadcast.sendMessage("tts_complete", {
                        status,
                    });
                }

                let completionMessage = "";
                let alertType = "success";

                if (status === "completed") {
                    completionMessage =
                        message ||
                        `Voice conversion completed: ${completed} done, ${partial} partial, ${errored} errored, ${skipped} skipped.`;
                    if (errored > 0 || partial > 0) {
                        alertType = "warning";
                    }
                } else if (status === "stopped") {
                    completionMessage =
                        "Voice conversion process was stopped by user.";
                    alertType = "warning";
                } else if (status === "error") {
                    completionMessage =
                        message || "Voice conversion process encountered an error.";
                    alertType = "error";
                }

                addLog(completionMessage, alertType);
                notify(
                    alertType,
                    status === "completed"
                        ? "Success!"
                        : status === "stopped"
                          ? "Stopped"
                          : "Error",
                    completionMessage,
                );
            }, 1000);
        }
    }

    function renderBreakdown({ completed, partial, errored, skipped, total }) {
        const el = document.getElementById("ttsBreakdown");
        if (!el) return;

        el.innerHTML = `
            <span class="badge badge-success mr-1">Completed: ${completed}</span>
            <span class="badge badge-warning mr-1">Partial: ${partial}</span>
            <span class="badge badge-danger mr-1">Errored: ${errored}</span>
            <span class="badge badge-secondary mr-1">Skipped: ${skipped}</span>
            <span class="badge badge-light">Total: ${total}</span>
        `;
    }

    function renderLanguageBreakdown(byLanguage, { fromReport = false } = {}) {
        const body = document.getElementById("languageBreakdownBody");
        if (!body || !byLanguage) return;

        const rows = Array.isArray(byLanguage)
            ? byLanguage
            : Object.values(byLanguage);

        if (rows.length === 0) {
            body.innerHTML = `<tr><td colspan="6" class="text-muted">No data yet.</td></tr>`;
            return;
        }

        body.innerHTML = rows
            .map((row) => {
                const notStarted =
                    row.not_started ??
                    Math.max(
                        0,
                        (row.total || 0) -
                            (row.completed || 0) -
                            (row.partial || 0) -
                            (row.errored || 0) -
                            (fromReport ? 0 : row.skipped || 0),
                    );

                return `
                    <tr>
                        <td>${row.name}</td>
                        <td class="text-success">${row.completed || 0}</td>
                        <td class="text-warning">${row.partial || 0}</td>
                        <td class="text-danger">${row.errored || 0}</td>
                        <td class="text-muted">${row.skipped || 0}</td>
                        <td>${notStarted}</td>
                    </tr>
                `;
            })
            .join("");
    }

    function renderRecentErrors(recentErrors) {
        const body = document.getElementById("recentErrorsBody");
        if (!body || !recentErrors) return;

        if (recentErrors.length === 0) {
            body.innerHTML = `<tr><td colspan="4" class="text-muted">No errors so far.</td></tr>`;
            return;
        }

        body.innerHTML = [...recentErrors]
            .reverse()
            .slice(0, 50)
            .map(
                (err) => `
                    <tr>
                        <td>${err.at}</td>
                        <td>#${err.question_id}</td>
                        <td>${err.language}</td>
                        <td>${(err.fields || []).join(", ")}${err.reason ? ` — ${err.reason}` : ""}</td>
                    </tr>
                `,
            )
            .join("");
    }

    function renderNeedsAttention(rows) {
        const body = document.getElementById("recentErrorsBody");
        if (!body) return;

        if (!rows || rows.length === 0) {
            body.innerHTML = `<tr><td colspan="4" class="text-muted">Nothing needs attention.</td></tr>`;
            return;
        }

        body.innerHTML = rows
            .map(
                (row) => `
                    <tr>
                        <td>${row.status}</td>
                        <td>#${row.question_id}</td>
                        <td>${row.language?.name || row.language_id}</td>
                        <td>${row.error || ""}</td>
                    </tr>
                `,
            )
            .join("");
    }

    function showProgressAlert({ percent, processed, total, message }) {
        const progressHtml = `
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     style="width: ${percent}%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">${processed} / ${total}</small>
                <small class="text-primary font-weight-bold">${Math.round(percent)}%</small>
            </div>
            <div class="text-muted" style="font-size: 13px;">${message}</div>
        `;

        requestAnimationFrame(() => {
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
                        popup: "translation-progress-popup shadow-sm",
                        title: "translation-progress-title",
                        closeButton: "translation-progress-close",
                        container: "translation-progress-container",
                    },
                    didOpen: () => {
                        const popup = Swal.getPopup();
                        popup.style.position = "fixed";
                        popup.style.bottom = "20px";
                        popup.style.right = "20px";
                        popup.style.margin = "0";
                        popup.style.border = "none";
                        popup.style.borderRadius = "8px";
                        popup.style.background = "#fff";
                        popup.style.boxShadow = "0 0 15px rgba(0,0,0,0.1)";

                        const title = popup.querySelector(".swal2-title");
                        if (title) {
                            title.style.fontSize = "14px";
                            title.style.padding = "0";
                            title.style.marginBottom = "10px";
                        }

                        const closeButton = popup.querySelector(".swal2-close");
                        if (closeButton) {
                            closeButton.style.fontSize = "20px";
                            closeButton.style.padding = "0";
                            closeButton.style.marginRight = "5px";
                            closeButton.style.marginTop = "2px";
                        }

                        const container =
                            document.querySelector(".swal2-container");
                        if (container) {
                            container.style.position = "fixed";
                            container.style.padding = "0";
                            container.style.background = "none";
                        }
                    },
                    willClose: () => {
                        if (isTtsActive) {
                            return false;
                        }
                        progressAlert = null;
                    },
                });
            } else {
                Swal.update({ html: progressHtml });
            }
        });
    }

    function closeProgressAlert() {
        if (progressAlert) {
            progressAlert.close();
            progressAlert = null;
        }
    }

    function resetButtonStates() {
        startBtn.disabled = false;
        stopBtn.disabled = true;
        isTtsActive = false;
    }

    function notify(icon, title, text) {
        Swal.fire({
            title,
            text,
            icon,
            position: "bottom-end",
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            timerProgressBar: true,
        });
    }

    const logQueue = [];
    let isProcessingLogs = false;

    function processLogQueue() {
        if (!isProcessingLogs && logQueue.length > 0) {
            isProcessingLogs = true;
            requestAnimationFrame(() => {
                const logsDiv = document.getElementById("logs");
                if (logsDiv) {
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
}
