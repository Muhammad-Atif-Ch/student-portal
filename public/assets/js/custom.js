/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$(function () {
    // Prevent multiple initializations
    if (window._settingsInitialized) {
        return;
    }
    window._settingsInitialized = true;

    let isLoading = true; // Flag to track if index API is still loading
    let updateTimer = null; // Timer for debounced updates

    $(document).ready(function () {
        initializeDeleteConfirm(); // Initialize delete confirmation dialogs
        initializeFetchThemeSettings(); // Fetch and apply theme settings
    });

    function initializeFetchThemeSettings() {
        // Only fetch settings if we have the routes
        if (routes && routes.setting && routes.setting.index) {
            fetchThemeSettings(); // Fetch settings on page load
        }

        // Event listeners for settings changes
        $(
            ".select-layout, .select-sidebar, #mini_sidebar_setting, #sticky_header_setting",
        ).on("change", function () {
            triggerSettingsUpdate();
        });

        $(".choose-theme li").on("click", function () {
            $(".choose-theme li").removeClass("active");
            $(this).addClass("active");
            triggerSettingsUpdate();
        });

        // Check if routes and setting routes are available
        if (!routes || !routes.setting || !routes.setting.index) {
            $(".loader").fadeOut("slow");
            return; // Exit if routes are not available
        }

        // Add navigation handler
        $(document).on("click", "a", function () {
            // Don't show loader for # links or external links
            if (
                this.getAttribute("href") &&
                !this.getAttribute("href").startsWith("#") &&
                !this.getAttribute("href").startsWith("http") &&
                !this.getAttribute("href").startsWith("javascript")
            ) {
                $(".loader").fadeIn("slow");
            }
        });

        // Hide loader if page load takes too long
        setTimeout(() => {
            $(".loader").fadeOut("slow");
        }, 5000);
    }
    // Function to trigger settings update with debounce
    function triggerSettingsUpdate() {
        if (updateTimer) {
            clearTimeout(updateTimer);
        }
        updateTimer = setTimeout(function () {
            updateLayoutSettings();
        }, 1000);
    }

    // Fetch user settings and apply them before hiding the loader
    function fetchThemeSettings() {
        if (isLoading) {
            // Prevent duplicate fetches
            $.ajax({
                url: routes.setting.index,
                type: "GET",
                success: function (response) {
                    let settings = response.setting;

                    // Apply settings only if elements exist
                    if (
                        $(
                            ".select-layout[value='" +
                                settings.theme_layout +
                                "']",
                        ).length
                    ) {
                        $(
                            ".select-layout[value='" +
                                settings.theme_layout +
                                "']",
                        ).prop("checked", true);
                    }

                    if (
                        $(
                            ".select-sidebar[value='" +
                                settings.sidebar_color +
                                "']",
                        ).length
                    ) {
                        $(
                            ".select-sidebar[value='" +
                                settings.sidebar_color +
                                "']",
                        ).prop("checked", true);
                    }

                    if (
                        $(
                            ".choose-theme li[title='" +
                                settings.color_theme +
                                "']",
                        ).length
                    ) {
                        $(".choose-theme li").removeClass("active");
                        $(
                            ".choose-theme li[title='" +
                                settings.color_theme +
                                "']",
                        ).addClass("active");
                    }

                    if ($("#mini_sidebar_setting").length) {
                        $("#mini_sidebar_setting").prop(
                            "checked",
                            Boolean(Number(settings.mini_sidebar)),
                        );
                    }

                    if ($("#sticky_header_setting").length) {
                        $("#sticky_header_setting").prop(
                            "checked",
                            Boolean(Number(settings.stiky_header)),
                        );
                    }

                    isLoading = false;
                },
                error: function (xhr) {
                    console.error("Error fetching settings:", xhr.responseText);
                    isLoading = false;
                },
                complete: function () {
                    // Always hide loader
                    $(".loader").fadeOut("slow");
                },
            });
        }
    }

    // Update settings via API
    function updateLayoutSettings() {
        const data = {
            theme_layout: $(".select-layout:checked").val(),
            sidebar_color: $(".select-sidebar:checked").val(),
            color_theme: $(".choose-theme li.active").attr("title"),
            mini_sidebar: $("#mini_sidebar_setting").prop("checked") ? 1 : 0,
            stiky_header: $("#sticky_header_setting").prop("checked") ? 1 : 0,
        };

        // Validate data before sending
        if (!data.theme_layout || !data.sidebar_color || !data.color_theme) {
            console.warn("Invalid settings data:", data);
            return;
        }

        $.ajax({
            url: routes.setting.update,
            type: "POST",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                console.log("Settings updated successfully:", response);
            },
            error: function (xhr) {
                console.error("Error updating settings:", xhr.responseText);
            },
        });
    }

    // Utility: Debounce function to prevent multiple API calls
    function debounce(func, delay) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, arguments), delay);
        };
    }
});

// Translation Progress Handler Functions
function initTranslationProgress() {
    // Prevent multiple initializations
    if (window._translationProgressInitialized) {
        return;
    }
    window._translationProgressInitialized = true;

    let progressAlert = null;
    let intervalId = null;
    let isTranslationActive = false;
    let lastProgressUpdate = 0;
    let isInitialCheck = true;

    // Wait for broadcast to be available
    if (typeof TranslationBroadcast === "undefined") {
        console.error("TranslationBroadcast not loaded");
        return;
    }

    // Setup event listeners
    setupEventListeners();

    // Always check for existing progress on any page
    checkExistingProgress();

    function setupEventListeners() {
        // Listen for broadcast events
        window.addEventListener("translation_progress_update", (event) => {
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

        window.addEventListener("translation_complete", (event) => {
            isTranslationActive = false;
            if (progressAlert) {
                progressAlert.close();
                progressAlert = null;
            }
            stopProgressPolling();
        });

        // Setup form handlers only if on translation page
        const form = document.getElementById("translationForm");
        if (form) {
            setupTranslationForm(form);
        }

        // Handle page visibility changes
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible" && isTranslationActive) {
                // Restart polling if it was active
                startProgressPolling();
            }
        });

        // Handle before unload to clean up
        window.addEventListener("beforeunload", () => {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        });
    }

    function startProgressPolling() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        isTranslationActive = true;
        // Only do immediate fetch if this isn't from the initial page load check
        if (!isInitialCheck) {
            fetchProgress();
        }
        isInitialCheck = false;
        // Then set up the interval
        intervalId = setInterval(fetchProgress, 5000);
    }

    function checkExistingProgress() {
        const inProgress = localStorage.getItem("translationInProgress");
        const progressData = localStorage.getItem("translationProgress");

        if (inProgress === "true" && progressData) {
            try {
                const data = JSON.parse(progressData);
                const status = data.progress?.status || data.status;

                if (status === "running") {
                    isTranslationActive = true;
                    startProgressPolling();

                    // Only update button states if on translation page
                    const startBtn = document.getElementById("startBtn");
                    const stopBtn = document.getElementById("stopBtn");
                    if (startBtn && stopBtn) {
                        startBtn.disabled = true;
                        stopBtn.disabled = false;
                    }

                    // Show existing progress immediately
                    updateProgressUI(data);
                }
            } catch (e) {
                console.warn("Failed to parse progress data:", e);
                resetButtonStates();
            }
        }
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

    async function fetchProgress() {
        if (!isTranslationActive) return;

        try {
            const response = await fetch(routes.translation.progress, {
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
                // Show error message
                addLog("Error in translation process: " + data.error, "error");
                Swal.fire({
                    title: "Error",
                    text:
                        "Translation process encountered an error: " +
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
            // Show error message
            addLog(
                "Failed to fetch translation progress: " + error.message,
                "error",
            );
            Swal.fire({
                title: "Error",
                text: "Failed to fetch translation progress",
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
            isTranslationActive = true;
            // Update localStorage
            localStorage.setItem(
                "translationProgress",
                JSON.stringify({
                    percentage: percent,
                    progress: {
                        completed,
                        total,
                        message,
                        status,
                    },
                }),
            );

            // Update button states
            const startBtn = document.getElementById("startBtn");
            const stopBtn = document.getElementById("stopBtn");
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
                            percent,
                        )}%</small>
                    </div>
                    <div class="text-muted" style="font-size: 13px;">${message}</div>
                `;

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
                            // Add custom styles to the alert
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
                            // Only allow closing if translation is not active
                            if (isTranslationActive) {
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
            isTranslationActive = false;
            setTimeout(() => {
                if (progressAlert) {
                    progressAlert.close();
                    progressAlert = null;
                }
                localStorage.removeItem("translationProgress");
                localStorage.removeItem("translationInProgress");
                TranslationBroadcast.sendMessage("translation_complete", {
                    status,
                });

                // Reset buttons
                const startBtn = document.getElementById("startBtn");
                const stopBtn = document.getElementById("stopBtn");
                if (startBtn && stopBtn) {
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                }

                // Show completion message
                let completionMessage = "";
                let alertType = "success";

                if (status === "completed") {
                    completionMessage =
                        "Translation process completed successfully!";
                } else if (status === "stopped") {
                    completionMessage =
                        "Translation process was stopped by user.";
                    alertType = "warning";
                } else if (status === "error") {
                    completionMessage =
                        "Translation process encountered an error.";
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
                        popup: "translation-complete-popup shadow-sm",
                    },
                    didOpen: (toast) => {
                        toast.style.marginBottom = "20px";
                        toast.style.marginRight = "20px";
                    },
                });
            }, 1000);
        }
    }

    function resetButtonStates() {
        const startBtn = document.getElementById("startBtn");
        const stopBtn = document.getElementById("stopBtn");
        if (startBtn && stopBtn) {
            startBtn.disabled = false;
            stopBtn.disabled = true;
            isTranslationActive = false;
        }
    }

    function setupTranslationForm(form) {
        const startBtn = document.getElementById("startBtn");
        const stopBtn = document.getElementById("stopBtn");
        const logsDiv = document.getElementById("logs");
        const clearLogsBtn = document.getElementById("clearLogs");
        let isSubmitting = false;

        // Form submit handler
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            if (isSubmitting || isTranslationActive) return;
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

            // Show initial progress alert immediately
            const initialProgressHtml = `
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">0 / 0</small>
                    <small class="text-primary font-weight-bold">0%</small>
                </div>
                <div class="text-muted" style="font-size: 13px;">Initializing translation process...</div>
            `;

            progressAlert = Swal.fire({
                title: '<small class="text-primary">Translation Progress</small>',
                html: initialProgressHtml,
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
                    const closeButton = popup.querySelector(".swal2-close");
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
                    // Only allow closing if translation is not active
                    if (isTranslationActive) {
                        return false;
                    }
                    progressAlert = null;
                },
            });

            try {
                const response = await fetch(routes.translation.start, {
                    method: "POST",
                    body: new FormData(form),
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'input[name="_token"]',
                        ).value,
                    },
                });

                const data = await response.json();
                if (data.success) {
                    addLog("Translation process started", "success");
                    localStorage.setItem("translationInProgress", "true");
                    isTranslationActive = true;
                    startProgressPolling();
                } else {
                    addLog(
                        `Error: ${
                            data.message || "Failed to start translation"
                        }`,
                        "error",
                    );
                    resetButtonStates();
                    if (progressAlert) {
                        progressAlert.close();
                        progressAlert = null;
                    }
                }
            } catch (error) {
                addLog("Network error occurred", "error");
                resetButtonStates();
                if (progressAlert) {
                    progressAlert.close();
                    progressAlert = null;
                }
            } finally {
                isSubmitting = false;
                startBtn.innerHTML = "Start Translation";
            }
        });

        // Stop button handler
        let isStoppingTranslation = false;
        stopBtn.addEventListener("click", async () => {
            if (isStoppingTranslation || !isTranslationActive) return;
            isStoppingTranslation = true;

            stopBtn.disabled = true;
            stopBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Stopping...';

            try {
                const response = await fetch(routes.translation.stop, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'input[name="_token"]',
                        ).value,
                    },
                });
                const data = await response.json();
                if (data.success) {
                    addLog("Translation stopped", "warning");
                    stopProgressPolling();
                    if (progressAlert) {
                        progressAlert.close();
                        progressAlert = null;
                    }
                } else {
                    addLog("Failed to stop translation", "error");
                    stopBtn.disabled = false;
                }
            } catch (error) {
                addLog("Failed to stop translation", "error");
                stopBtn.disabled = false;
            } finally {
                isStoppingTranslation = false;
                stopBtn.innerHTML = "Stop Translation";
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
}

// Initialize translation progress handler - with check for multiple initializations
if (!window._translationProgressInitialized) {
    document.addEventListener("DOMContentLoaded", initTranslationProgress);
}

// TTS Progress Handler Functions
function initTTSProgress() {
    // Prevent multiple initializations
    if (window._ttsProgressInitialized) {
        return;
    }
    window._ttsProgressInitialized = true;

    let progressAlert = null;
    let intervalId = null;
    let isConversionActive = false;
    let lastProgressUpdate = 0;
    let isInitialCheck = true;

    // Wait for broadcast to be available
    if (typeof TranslationBroadcast === "undefined") {
        console.error("TranslationBroadcast not loaded");
        return;
    }

    // Setup event listeners
    setupEventListeners();

    // Always check for existing progress on any page
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

        // Setup form handlers only if on TTS page
        const form = document.getElementById("ttsForm");
        if (form) {
            setupTTSForm(form);
        }

        // Handle page visibility changes
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible" && isConversionActive) {
                // Restart polling if it was active
                startProgressPolling();
            }
        });

        // Handle before unload to clean up
        window.addEventListener("beforeunload", () => {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        });
    }

    function startProgressPolling() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }

        isConversionActive = true;
        // Only do immediate fetch if this isn't from the initial page load check
        if (!isInitialCheck) {
            fetchProgress();
        }
        isInitialCheck = false;
        // Then set up the interval
        intervalId = setInterval(fetchProgress, 10000);
    }

    function checkExistingProgress() {
        const inProgress = localStorage.getItem("ttsInProgress");
        const progressData = localStorage.getItem("ttsProgress");

        if (inProgress === "true" && progressData) {
            try {
                const data = JSON.parse(progressData);
                const status = data.progress?.status || data.status;

                if (status === "running") {
                    isConversionActive = true;
                    startProgressPolling();

                    // Only update button states if on TTS page
                    const startBtn = document.getElementById("startVoiceBtn");
                    const stopBtn = document.getElementById("stopVoiceBtn");
                    if (startBtn && stopBtn) {
                        startBtn.disabled = true;
                        stopBtn.disabled = false;
                    }

                    // Show existing progress immediately
                    updateProgressUI(data);
                }
            } catch (e) {
                console.warn("Failed to parse progress data:", e);
                resetButtonStates();
            }
        }
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
                // Show error message
                if (document.getElementById("logs")) {
                    addLog(
                        "Error in conversion process: " + data.error,
                        "error",
                    );
                }
                Swal.fire({
                    title: "Error",
                    text:
                        "Voice conversion encountered an error: " + data.error,
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
            // Show error message
            if (document.getElementById("logs")) {
                addLog(
                    "Failed to fetch conversion progress: " + error.message,
                    "error",
                );
            }
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
                }),
            );
            localStorage.setItem("ttsInProgress", "true");

            // Update button states if on TTS page
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
                            percent,
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
                            popup: "translation-progress-popup shadow-sm",
                            title: "translation-progress-title",
                            closeButton: "translation-progress-close",
                            container: "translation-progress-container",
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

                // Reset buttons if on TTS page
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
                    completionMessage = message;
                    alertType = "error";
                }

                // Add to logs if on TTS page
                if (document.getElementById("logs")) {
                    addLog(completionMessage, alertType);
                }

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
                        popup: "translation-complete-popup shadow-sm",
                    },
                    didOpen: (toast) => {
                        toast.style.marginBottom = "20px";
                        toast.style.marginRight = "20px";
                    },
                });
            }, 1000);
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
                            'input[name="_token"]',
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
                        "error",
                    );
                    resetButtonStates();
                    if (progressAlert) {
                        progressAlert.close();
                        progressAlert = null;
                    }
                }
            } catch (error) {
                addLog("Network error occurred", "error");
                resetButtonStates();
                if (progressAlert) {
                    progressAlert.close();
                    progressAlert = null;
                }
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
                            'input[name="_token"]',
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
        if (document.getElementById("logs")) {
            logQueue.push({ message, type });
            processLogQueue();
        }
    }
}

// Initialize TTS progress handler - with check for multiple initializations
if (!window._ttsProgressInitialized) {
    document.addEventListener("DOMContentLoaded", initTTSProgress);
}

function initializeDeleteConfirm() {
    $(document).on("submit", ".delete-form", function (e) {
        e.preventDefault();

        let form = this;

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleted!",
                    text: "Your file has been deleted.",
                    icon: "success",
                });
                form.submit();
            }
        });
    });
}
