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

    let updateTimer = null; // Timer for debounced updates

    $(document).ready(function () {
        initializeDeleteConfirm(); // Initialize delete confirmation dialogs
        initializeThemeSettings(); // Apply settings from window.appSettings + wire up controls
    });

    function initializeThemeSettings() {
        // Settings are now rendered server-side into window.appSettings
        // (no AJAX round-trip) - just apply them to the UI controls.
        applySettings(window.appSettings || {});
        $(".loader").fadeOut("slow");

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

        // Loader on navigation
        $(document).on("click", "a", function () {
            if (
                this.getAttribute("href") &&
                !this.getAttribute("href").startsWith("#") &&
                !this.getAttribute("href").startsWith("http") &&
                !this.getAttribute("href").startsWith("javascript")
            ) {
                $(".loader").fadeIn("slow");
            }
        });

        // Safety net: hide loader if something takes too long
        setTimeout(() => {
            $(".loader").fadeOut("slow");
        }, 5000);
    }

    function applySettings(settings) {
        if (!settings) return;

        if ($(".select-layout[value='" + settings.theme_layout + "']").length) {
            $(".select-layout[value='" + settings.theme_layout + "']").prop(
                "checked",
                true,
            );
        }

        if (
            $(".select-sidebar[value='" + settings.sidebar_color + "']").length
        ) {
            $(".select-sidebar[value='" + settings.sidebar_color + "']").prop(
                "checked",
                true,
            );
        }

        if (
            $(".choose-theme li[title='" + settings.color_theme + "']").length
        ) {
            $(".choose-theme li").removeClass("active");
            $(
                ".choose-theme li[title='" + settings.color_theme + "']",
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
                // Keep window.appSettings in sync so a later page load
                // (before cache refresh timing) still reflects reality.
                window.appSettings = Object.assign(
                    {},
                    window.appSettings,
                    data,
                );
            },
            error: function (xhr) {
                console.error("Error updating settings:", xhr.responseText);
            },
        });
    }

    window.initSimpleDataTable = function initSimpleDataTable(
        selector,
        options = {},
    ) {
        const $table = $(selector);
        if ($table.length === 0) return null;

        const defaults = {
            language: { emptyTable: "No data found" },
        };
        options = $.extend(true, {}, defaults, options);

        try {
            if ($.fn.DataTable.isDataTable($table[0])) {
                $table.DataTable().destroy();
            }
        } catch (e) {
            console.warn(`DataTable destroy failed for ${selector}:`, e);
        }

        try {
            return $table.DataTable(options);
        } catch (e) {
            console.error(`DataTable init failed for ${selector}:`, e);
            return null;
        }
    };
});

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
