/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$(function () {
    initTranslationProgress();
});

// Translation Progress Handler Functions
function initTranslationProgress() {
    // Prevent multiple initializations
    if (window._translationProgressInitialized) {
        return;
    }

    const startBtn = document.getElementById("startBtnTranslation");
    const stopBtn = document.getElementById("stopBtnTranslation");
    const reportBtn = document.getElementById("viewReportBtnTranslation");

    // Only run on pages that actually have these buttons.
    if (!startBtn || !stopBtn) {
        return;
    }

    if (typeof routes === "undefined" || !routes.translation) {
        console.error("routes.translation is not defined");
        return;
    }

    window._translationProgressInitialized = true;

    let progressAlert = null;
    let intervalId = null;
    let isTranslationActive = false;
    let isSubmitting = false;
    let isStopping = false;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // Wait for broadcast to be available (used to sync state across tabs,
    // same as the TTS page).
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
            window.addEventListener("translation_progress_update", (event) => {
                if (!event.detail) return;
                updateProgressUI(event.detail);
            });

            window.addEventListener("translation_complete", () => {
                isTranslationActive = false;
                closeProgressAlert();
                stopProgressPolling();
            });
        }

        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible" && isTranslationActive) {
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

    // ---- Start ----
    async function handleStart() {
        if (isSubmitting || isTranslationActive) return;
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
            message: "Initializing translation process...",
        });

        try {
            const response = await fetch(routes.translation.start, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (data.success) {
                addLog("Translation process started", "success");
                localStorage.setItem("translationInProgress", "true");
                isTranslationActive = true;
                startProgressPolling(false);
            } else {
                addLog(
                    `Error: ${data.error || data.message || "Failed to start translation"}`,
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

    // ---- Stop ----
    async function handleStop() {
        if (isStopping || !isTranslationActive) return;
        isStopping = true;

        stopBtn.disabled = true;
        const originalStopHtml = stopBtn.innerHTML;
        stopBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Stopping...';

        try {
            const response = await fetch(routes.translation.stop, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });

            const data = await response.json();

            if (data.success) {
                addLog("Translation stopped", "warning");
                if (hasBroadcast) {
                    TranslationBroadcast.sendMessage("translation_complete", {
                        status: "stopped",
                    });
                }
                stopProgressPolling();
                closeProgressAlert();
            } else {
                addLog("Failed to stop translation", "error");
                stopBtn.disabled = false;
            }
        } catch (error) {
            addLog("Failed to stop translation: " + error.message, "error");
            stopBtn.disabled = false;
        } finally {
            isStopping = false;
            stopBtn.innerHTML = originalStopHtml;
        }
    }

    // ---- Report (authoritative, DB-backed - works even when idle) ----
    async function handleViewReport() {
        const originalHtml = reportBtn.innerHTML;
        reportBtn.disabled = true;
        reportBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

        try {
            const response = await fetch(routes.translation.report, {
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
                .getElementById("translationReportPanel")
                ?.classList.remove("d-none");
        } catch (error) {
            console.error("Failed to fetch report:", error);
            notify("error", "Error", "Failed to load the translation report");
        } finally {
            reportBtn.disabled = false;
            reportBtn.innerHTML = originalHtml;
        }
    }

    // ---- Polling ----
    function startProgressPolling(immediate) {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        isTranslationActive = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;

        if (immediate) {
            fetchProgress();
        }
        intervalId = setInterval(fetchProgress, 5000);
    }

    function stopProgressPolling() {
        isTranslationActive = false;
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        localStorage.removeItem("translationInProgress");
        localStorage.removeItem("translationProgress");
        resetButtonStates();
    }

    function checkExistingProgress() {
        const inProgress = localStorage.getItem("translationInProgress");
        if (inProgress !== "true") {
            return;
        }
        // Don't trust stale localStorage blindly (e.g. tab was closed mid
        // run) - ask the server what's actually happening right now.
        fetchProgress(true);
    }

    async function fetchProgress(isInitialCheck = false) {
        try {
            const response = await fetch(routes.translation.progress, {
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
                addLog("Error in translation process: " + data.error, "error");
                notify(
                    "error",
                    "Error",
                    "Translation process encountered an error: " + data.error,
                );
                return;
            }

            const status = data.progress?.status || "idle";

            if (status === "running") {
                startProgressPolling(false);
                updateProgressUI(data);
            } else if (isInitialCheck) {
                // Nothing actually running server-side - clear stale state
                // instead of showing a progress bar that will never move.
                localStorage.removeItem("translationInProgress");
                localStorage.removeItem("translationProgress");
                resetButtonStates();
            } else {
                updateProgressUI(data);
            }
        } catch (error) {
            console.error("Failed to fetch progress:", error);
            stopProgressPolling();
            addLog(
                "Failed to fetch translation progress: " + error.message,
                "error",
            );
            notify("error", "Error", "Failed to fetch translation progress");
        }
    }

    function updateProgressUI(data) {
        if (!data) return;

        const percent = parseFloat(data.percentage ?? 0);
        const progress = data.progress || data;
        const message = progress.message || "Processing...";
        const status = progress.status || "running";

        const total = parseInt(progress.total || 0);
        // 'processed' is every pair looked at (drives the bar); the other
        // four are the actual outcome breakdown.
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
            isTranslationActive = true;
            localStorage.setItem(
                "translationProgress",
                JSON.stringify({
                    percentage: percent,
                    progress: { processed, total, message, status },
                }),
            );
            localStorage.setItem("translationInProgress", "true");

            startBtn.disabled = true;
            stopBtn.disabled = false;

            showProgressAlert({ percent, processed, total, message });
        }

        if (["completed", "stopped", "error"].includes(status)) {
            isTranslationActive = false;
            setTimeout(() => {
                closeProgressAlert();

                // This is the actual fix: stopProgressPolling() clears the
                // setInterval. Without it, polling kept running forever,
                // kept re-reading the cached "completed" status, and
                // re-fired this whole block every 5 seconds.
                stopProgressPolling();

                if (hasBroadcast) {
                    TranslationBroadcast.sendMessage("translation_complete", {
                        status,
                    });
                }

                let completionMessage = "";
                let alertType = "success";

                if (status === "completed") {
                    completionMessage =
                        message ||
                        `Translation completed: ${completed} done, ${partial} partial, ${errored} errored, ${skipped} skipped.`;
                    if (errored > 0 || partial > 0) {
                        alertType = "warning";
                    }
                } else if (status === "stopped") {
                    completionMessage =
                        "Translation process was stopped by user.";
                    alertType = "warning";
                } else if (status === "error") {
                    completionMessage =
                        message || "Translation process encountered an error.";
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

    // ---- Report / breakdown rendering ----
    function renderBreakdown({ completed, partial, errored, skipped, total }) {
        const el = document.getElementById("translationBreakdown");
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

        // Most recent first.
        body.innerHTML = [...recentErrors]
            .reverse()
            .slice(0, 50)
            .map(
                (err) => `
                    <tr>
                        <td>${err.at}</td>
                        <td>#${err.question_id}</td>
                        <td>${err.language}</td>
                        <td>${(err.fields || []).join(", ")}</td>
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

    // ---- UI helpers ----
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
                    title: '<small class="text-primary">Translation Progress</small>',
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
                        if (isTranslationActive) {
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
        isTranslationActive = false;
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

    // ---- Logs ----
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
