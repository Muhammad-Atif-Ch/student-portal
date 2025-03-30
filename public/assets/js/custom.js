/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$(function () {
    let isLoading = true; // Flag to track if index API is still loading

    $(document).ready(function () {
        fetchThemeSettings(); // Fetch settings on page load

        // Event listeners with debounced update
        $(
            ".select-layout, .select-sidebar, #mini_sidebar_setting, #sticky_header_setting"
        ).on("change", debounce(updateLayoutSettings, 1000));

        $(".choose-theme li").on("click", function () {
            $(".choose-theme li").removeClass("active");
            $(this).addClass("active");
            debounce(updateLayoutSettings, 1000);
        });
    });

    // Fetch user settings and apply them before hiding the loader
    function fetchThemeSettings() {
        $.ajax({
            url: routes.setting.index, // Laravel API route
            type: "GET",
            success: function (response) {
                let settings = response.setting;

                // Apply settings
                $(".select-layout[value='" + settings.theme_layout + "']")
                    .prop("checked", true)
                    .trigger("change");
                $(".select-sidebar[value='" + settings.sidebar_color + "']")
                    .prop("checked", true)
                    .trigger("change");
                $(".choose-theme li")
                    .removeClass("active")
                    .filter("[title='" + settings.color_theme + "']")
                    .addClass("active")
                    .trigger("click");
                $("#mini_sidebar_setting")
                    .prop("checked", Boolean(Number(settings.mini_sidebar)))
                    .trigger("change");
                $("#sticky_header_setting")
                    .prop("checked", Boolean(Number(settings.stiky_header)))
                    .trigger("change");

                isLoading = false;

                // Hide loader after theme is applied
                $(".loader").fadeOut("slow");
            },
            error: function (xhr) {
                isLoading = false;
                console.error("Error fetching settings:", xhr.responseText);
            },
        });
    }

    // Update settings via API (debounced)
    function updateLayoutSettings() {
        let data = {
            theme_layout: $(".select-layout:checked").val(),
            sidebar_color: $(".select-sidebar:checked").val(),
            color_theme: $(".choose-theme li.active").attr("title"),
            mini_sidebar: $("#mini_sidebar_setting").prop("checked") ? 1 : 0,
            stiky_header: $("#sticky_header_setting").prop("checked") ? 1 : 0,
        };

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
