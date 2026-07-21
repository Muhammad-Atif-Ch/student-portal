(function () {
    let progressAlert = null;
    let isTranslationActive = false;
    let pollTimer = null;

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

    function showCompletionToast(message, icon = "success") {
        Swal.fire({
            toast: true,
            position: "bottom-end",
            icon,
            title: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }

    function closeProgressAlert() {
        isTranslationActive = false;
        if (progressAlert) {
            Swal.close();
            progressAlert = null;
        }
    }

    function poll() {
        fetch(window.routes.combined.progress, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then((res) => res.json())
            .then((data) => {
                const percent =
                    data.total > 0 ? (data.processed / data.total) * 100 : 0;

                if (data.status === "running") {
                    isTranslationActive = true;
                    showProgressAlert({
                        percent,
                        processed: data.processed,
                        total: data.total,
                        message: data.message || "Processing...",
                    });
                    pollTimer = setTimeout(poll, 2000);
                    return;
                }

                // Finished (completed / stopped / failed)
                closeProgressAlert();
                localStorage.removeItem("translationInProgress");

                if (data.status === "completed") {
                    showCompletionToast(
                        data.message || "Translation completed successfully",
                    );
                } else if (data.status === "stopped") {
                    showCompletionToast(
                        data.message || "Translation stopped",
                        "info",
                    );
                } else {
                    showCompletionToast(
                        data.message || "Translation failed",
                        "error",
                    );
                }
            })
            .catch(() => {
                // Network hiccup — retry, don't kill the toast
                pollTimer = setTimeout(poll, 3000);
            });
    }

    document.addEventListener("DOMContentLoaded", () => {
        if (!window.routes || !window.routes.combined.progress) {
            return; // route not injected on this page/layout
        }

        const inProgress = localStorage.getItem("translationInProgress");
        if (inProgress === "true") {
            isTranslationActive = true;
            poll();
        }
    });

    window.addEventListener("beforeunload", () => {
        if (pollTimer) clearTimeout(pollTimer);
    });
})();
