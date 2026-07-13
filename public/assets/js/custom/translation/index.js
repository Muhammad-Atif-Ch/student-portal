/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

(function () {
    initTranslationsIndex();

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initTranslationsIndex);
    } else {
        initTranslationsIndex();
    }
})();

function initTranslationsIndex() {
    if (window._translationsIndexInitialized) {
        return;
    }

    const app = document.getElementById("translationsApp");
    if (!app) {
        return; // Not on this page.
    }

    window._translationsIndexInitialized = true;

    const audioBaseUrl = app.dataset.audioBase;
    const retranslateUrlTemplate = routes.translation.retranslate;
    const reconvertUrlTemplate = routes.tts?.reconvert;
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!retranslateUrlTemplate) {
        console.error("routes.translation.retranslate is not defined");
    }

    if (!reconvertUrlTemplate) {
        console.error("routes.tts.reconvert is not defined");
    }

    const fieldLabels = {
        question: "Question",
        a: "Option A",
        b: "Option B",
        c: "Option C",
        d: "Option D",
        answer_explanation: "Answer Explanation",
    };
    const fieldOrder = ["question", "a", "b", "c", "d", "answer_explanation"];

    const modalEl = document.getElementById("translationDetailModal");
    const modalIdEl = document.getElementById("modalTranslationId");
    const modalMetaEl = document.getElementById("modalMeta");
    const modalFieldsBody = document.getElementById("modalFieldsBody");

    initDataTable();
    setupEventDelegation();

    function initDataTable() {
        if (typeof jQuery === "undefined" || !jQuery.fn.DataTable) {
            console.warn("DataTables/jQuery not loaded - skipping table init");
            return;
        }

        jQuery("#table-translations").DataTable({
            paging: false,
            ordering: true,
            searching: true,
        });
    }

    function setupEventDelegation() {
        document.addEventListener("click", (event) => {
            const viewBtn = event.target.closest(".view-translation-btn");
            if (viewBtn) {
                openTranslationModal(viewBtn);
                return;
            }

            const retranslateBtn = event.target.closest(".retranslate-btn");
            if (retranslateBtn) {
                handleRetranslate(retranslateBtn);
                return;
            }

            const regenerateAudioBtn = event.target.closest(
                ".regenerate-audio-btn",
            );
            if (regenerateAudioBtn) {
                handleRegenerateAudio(regenerateAudioBtn);
            }
        });
    }

    function openTranslationModal(btn) {
        const translationId = btn.dataset.translationId;
        const fields = safeJsonParse(btn.dataset.fields);
        const audios = safeJsonParse(btn.dataset.audios);

        modalIdEl.textContent = translationId;
        modalMetaEl.textContent = [
            `Quiz #${btn.dataset.quizId}`,
            `Question #${btn.dataset.questionId}`,
            btn.dataset.language,
            btn.dataset.error || null,
        ]
            .filter(Boolean)
            .join(" · ");

        modalFieldsBody.innerHTML = fieldOrder
            .map((key) =>
                fieldRowHtml(translationId, key, fields[key], audios[key]),
            )
            .join("");

        if (typeof jQuery !== "undefined") {
            jQuery(modalEl).modal("show");
        }
    }

    function fieldRowHtml(translationId, key, text, audioFile) {
        const label = fieldLabels[key] || key;
        const hasText = Boolean(text && text.trim().length);
        const audioSrc = audioFile ? `${audioBaseUrl}/${audioFile}` : null;

        return `
                <tr data-field-row="${key}">
                    <td class="field-name-cell">${label}</td>
                    <td class="translation-cell">
                        <div class="field-translation-text ${hasText ? "" : "is-empty"}">${
                            hasText ? escapeHtml(text) : "Not translated yet"
                        }</div>
                    </td>
                    <td class="audio-cell">
                        ${
                            audioSrc
                                ? `<audio controls src="${audioSrc}" class="audio-player"></audio>`
                                : `<span class="no-audio-badge">No audio yet</span>`
                        }
                    </td>
                    <td class="translate-action-cell">
                        <button type="button" class="btn btn-sm btn-outline-primary retranslate-btn"
                            data-translation-id="${translationId}" data-field="${key}"
                            title="${hasText ? "Re-translate this field" : "Translate this field"}">
                            <i class="fas fa-language"></i>
                        </button>
                    </td>
                    <td class="voice-action-cell">
                        <button type="button" class="btn btn-sm btn-outline-success regenerate-audio-btn"
                            data-translation-id="${translationId}" data-field="${key}"
                            title="${hasText ? "Regenerate voice for this field" : "Translate text first"}"
                            ${hasText ? "" : "disabled"}>
                            <i class="fas fa-volume-up"></i>
                        </button>
                    </td>
                </tr>
            `;
    }

    async function handleRetranslate(btn) {
        if (btn.classList.contains("is-loading")) {
            return;
        }

        const translationId = btn.dataset.translationId;
        const field = btn.dataset.field;
        const row = btn.closest("tr[data-field-row]");
        const textEl = row?.querySelector(".field-translation-text");
        const voiceBtn = row?.querySelector(".regenerate-audio-btn");

        if (!retranslateUrlTemplate) {
            notify("error", "Error", "Retranslate route is not configured.");
            return;
        }

        btn.classList.add("is-loading");
        btn.disabled = true;

        try {
            const url = retranslateUrlTemplate.replace(
                "ID_PLACEHOLDER",
                translationId,
            );
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({ field }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                throw new Error(
                    data.error || "Failed to translate this field.",
                );
            }

            if (textEl) {
                textEl.textContent = data.translation;
                textEl.classList.remove("is-empty");
            }
            btn.title = "Re-translate this field";

            if (voiceBtn) {
                voiceBtn.disabled = false;
                voiceBtn.title = "Regenerate voice for this field";
            }

            syncSummaryRowTranslation(translationId, field, data.translation);
            notify("success", "Updated", `"${field}" translated successfully.`);
        } catch (error) {
            notify(
                "error",
                "Error",
                error.message || "Failed to translate this field.",
            );
        } finally {
            btn.classList.remove("is-loading");
            btn.disabled = false;
        }
    }

    async function handleRegenerateAudio(btn) {
        if (btn.classList.contains("is-loading") || btn.disabled) {
            return;
        }

        const translationId = btn.dataset.translationId;
        const field = btn.dataset.field;
        const row = btn.closest("tr[data-field-row]");
        const audioCell = row?.querySelector(".audio-cell");

        if (!reconvertUrlTemplate) {
            notify("error", "Error", "Voice regenerate route is not configured.");
            return;
        }

        btn.classList.add("is-loading");
        btn.disabled = true;

        try {
            const url = reconvertUrlTemplate.replace(
                "ID_PLACEHOLDER",
                translationId,
            );
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({ field }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                throw new Error(
                    data.error || "Failed to regenerate voice for this field.",
                );
            }

            const audioUrl =
                data.audio_url || `${audioBaseUrl}/${data.audio}`;

            if (audioCell) {
                audioCell.innerHTML = `<audio controls src="${audioUrl}" class="audio-player"></audio>`;
            }

            btn.title = "Regenerate voice for this field";

            syncSummaryRowAudio(translationId, field, data.audio);
            notify("success", "Updated", `"${field}" voice regenerated successfully.`);
        } catch (error) {
            notify(
                "error",
                "Error",
                error.message || "Failed to regenerate voice for this field.",
            );
        } finally {
            btn.classList.remove("is-loading");
            btn.disabled = false;
        }
    }

    function syncSummaryRowTranslation(translationId, field, translation) {
        const row = document.querySelector(
            `tr[data-row-id="${translationId}"]`,
        );
        if (!row) {
            return;
        }

        const viewBtn = row.querySelector(".view-translation-btn");
        if (viewBtn) {
            const fields = safeJsonParse(viewBtn.dataset.fields);
            fields[field] = translation;
            viewBtn.dataset.fields = JSON.stringify(fields);
        }

        row.querySelector(`.field-dot[data-field="${field}"]`)?.classList.add(
            "is-done",
        );
    }

    function syncSummaryRowAudio(translationId, field, audioFile) {
        const row = document.querySelector(
            `tr[data-row-id="${translationId}"]`,
        );
        if (!row) {
            return;
        }

        const viewBtn = row.querySelector(".view-translation-btn");
        if (!viewBtn) {
            return;
        }

        const audios = safeJsonParse(viewBtn.dataset.audios);
        audios[field] = audioFile;
        viewBtn.dataset.audios = JSON.stringify(audios);

        const audioCount = Object.values(audios).filter(Boolean).length;
        const cells = row.querySelectorAll("td");
        if (cells[6]) {
            cells[6].textContent = `${audioCount} / 6`;
        }
    }

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str;
        return div.innerHTML;
    }

    function safeJsonParse(value) {
        try {
            return value ? JSON.parse(value) : {};
        } catch (error) {
            console.warn("Failed to parse dataset JSON:", error);
            return {};
        }
    }

    function notify(icon, title, text) {
        if (typeof Swal === "undefined") {
            console.log(`${title}: ${text}`);
            return;
        }
        Swal.fire({
            icon,
            title,
            text,
            position: "bottom-end",
            toast: true,
            timer: icon === "error" ? 3000 : 2000,
            showConfirmButton: false,
        });
    }
}
