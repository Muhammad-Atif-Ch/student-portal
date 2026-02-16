/**
 * Language Usage Date Range Filter
 * Handles real-time filtering of language usage data based on selected date range
 */

$(document).ready(function () {
    initializeDateRangePicker();
});

/**
 * Initialize the date range picker with predefined ranges
 */
function initializeDateRangePicker() {
    let start = moment().local().subtract(29, "days");
    let end = moment().local();

    let startUTC = start.clone().utc().format("YYYY-MM-DD HH:mm:ss");
    let endUTC = end.clone().utc().format("YYYY-MM-DD HH:mm:ss");

    updateButtonText(start, end);
    filterLanguageUsage(startUTC, endUTC);

    $(".daterange-btn").daterangepicker(
        {
            ranges: {
                Today: [moment().local(), moment().local()],
                Yesterday: [
                    moment().local().subtract(1, "days"),
                    moment().local().subtract(1, "days"),
                ],
                "Last 7 Days": [
                    moment().local().subtract(6, "days"),
                    moment().local(),
                ],
                "Last 30 Days": [
                    moment().local().subtract(29, "days"),
                    moment().local(),
                ],
                "This Month": [
                    moment().local().startOf("month"),
                    moment().local().endOf("month"),
                ],
                "Last Month": [
                    moment().local().subtract(1, "month").startOf("month"),
                    moment().local().subtract(1, "month").endOf("month"),
                ],
            },
            startDate: start,
            endDate: end,
        },
        function (start, end) {
            let startUTC = start.clone().utc().format("YYYY-MM-DD HH:mm:ss");
            let endUTC = end.clone().utc().format("YYYY-MM-DD HH:mm:ss");

            console.log("Send To API (UTC):", startUTC, endUTC);

            // Update button text with selected date range
            updateButtonText(start, end);
            // Filter data based on selected dates
            filterLanguageUsage(startUTC, endUTC);
        },
    );
}

/**
 * Update the date range button text
 * @param {moment} start - Start date
 * @param {moment} end - End date
 */
function updateButtonText(start, end) {
    $(".daterange-btn span").html(
        start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"),
    );
}

/**
 * Filter language usage data via AJAX
 * @param {string} startDate - Start date in YYYY-MM-DD format
 * @param {string} endDate - End date in YYYY-MM-DD format
 */
function filterLanguageUsage(startDate, endDate) {
    const url = routes.dashboard.languageUsage;

    // Validate URL
    if (!url) {
        console.error(
            "Filter URL not found. Please add data-url attribute to the button.",
        );
        return;
    }

    $.ajax({
        url: url,
        type: "GET",
        data: {
            start_date: startDate,
            end_date: endDate,
        },
        dataType: "json",
        beforeSend: function () {
            showLoadingState();
        },
        success: function (response) {
            if (response.success && response.data) {
                updateTable(response.data);
            } else {
                showErrorMessage("Invalid response format");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error filtering data:", error);
            showErrorMessage("Failed to filter data. Please try again.");
        },
        complete: function () {
            // Optional: Hide loading spinner if you have one
        },
    });
}

/**
 * Show loading state in the table
 */
function showLoadingState() {
    const loadingHtml = `
        <tr>
            <td colspan="3" class="text-center">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <span class="ml-2">Loading data...</span>
            </td>
        </tr>
    `;
    $("tbody.language-usage-report").html(loadingHtml);
}

/**
 * Show error message in the table
 * @param {string} message - Error message to display
 */
function showErrorMessage(message) {
    const errorHtml = `
        <tr>
            <td colspan="3" class="text-center text-danger">
                <i class="fas fa-exclamation-triangle"></i> ${message}
            </td>
        </tr>
    `;
    $("tbody.language-usage-report").html(errorHtml);
}

/**
 * Update the table with filtered data
 * @param {Array} data - Array of language usage data
 */
function updateTable(data) {
    let html = "";

    if (data.length === 0) {
        html = `
            <tr>
                <td colspan="3" class="text-center text-muted">
                    <i class="fas fa-info-circle"></i> No data found for the selected date range
                </td>
            </tr>
        `;
    } else {
        data.forEach((item, index) => {
            html += buildTableRow(item, index + 1);
        });
    }

    $("tbody.language-usage-report").html(html);

    // Optional: Add fade-in animation
    $("tbody.language-usage-report tr").hide().fadeIn(300);
}

/**
 * Build a single table row
 * @param {Object} item - Language usage item
 * @param {number} iteration - Row number
 * @returns {string} HTML string for table row
 */
function buildTableRow(item, iteration) {
    return `
        <tr>
            <td>${iteration}</td>
            <td>${item.language.name}</td>
            <td>${item.total}</td>
        </tr>
    `;
}
