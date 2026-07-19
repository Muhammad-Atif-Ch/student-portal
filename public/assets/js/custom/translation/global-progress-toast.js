"use strict";

$(function () {
    if (typeof routes === "undefined" || !routes.combined) return;
    if (document.getElementById("translationsApp")) return; // full UI page handles its own display

    const inProgress = localStorage.getItem("combinedInProgress");
    if (inProgress !== "true") return;

    let toast = null;
    poll();
    const interval = setInterval(poll, 4000);

    async function poll() {
        try {
            const res = await fetch(routes.combined.progress, {
                headers: { Accept: "application/json" },
            });
            const data = await res.json();
            const status = data.progress?.status;

            if (status !== "running") {
                localStorage.removeItem("combinedInProgress");
                clearInterval(interval);
                if (toast) toast.close();
                return;
            }

            const html = `<div style="min-width:220px">
                <div class="progress mb-1" style="height:6px"><div class="progress-bar" style="width:${data.percentage}%"></div></div>
                <small>${data.progress.processed}/${data.progress.total} — ${data.percentage}%</small>
            </div>`;

            if (!toast) {
                toast = Swal.fire({
                    title: "Translation + Voice running",
                    html,
                    position: "bottom-end",
                    toast: true,
                    showConfirmButton: false,
                    showCloseButton: true,
                    allowOutsideClick: false,
                    backdrop: false,
                    width: 260,
                });
            } else {
                Swal.update({ html });
            }
        } catch (e) {
            /* silent */
            console.error(
                "Error fetching translation progress:",
                e.$message || e,
            );
        }
    }
});
