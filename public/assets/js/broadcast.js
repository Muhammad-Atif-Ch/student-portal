// Broadcast Channel Handler
const TranslationBroadcast = {
    channel: null,
    isInitialized: false,

    // Initialize the broadcast channel
    init() {
        if (this.isInitialized) return true;

        try {
            if (!window.BroadcastChannel) {
                console.warn("BroadcastChannel not supported in this browser");
                return false;
            }

            this.channel = new BroadcastChannel("translation_progress");
            this.setupListeners();
            this.isInitialized = true;

            // Check for existing progress on init
            const savedProgress = localStorage.getItem("translationProgress");
            if (savedProgress) {
                try {
                    const progress = JSON.parse(savedProgress);
                    if (progress && progress.status === "running") {
                        this.handleMessage("progress_update", progress);
                    }
                } catch (e) {
                    console.warn("Failed to parse saved progress:", e);
                }
            }

            return true;
        } catch (error) {
            console.error("Failed to initialize broadcast channel:", error);
            return false;
        }
    },

    // Setup message listeners
    setupListeners() {
        if (!this.channel) return;

        this.channel.onmessage = (event) => {
            try {
                if (!event.data || typeof event.data !== "object") {
                    console.warn(
                        "Invalid broadcast message format:",
                        event.data
                    );
                    return;
                }

                const { type, data } = event.data;
                if (!type) {
                    console.warn("Missing message type:", event.data);
                    return;
                }

                this.handleMessage(type, data);
            } catch (error) {
                console.error("Error handling broadcast message:", error);
            }
        };

        this.channel.onmessageerror = (error) => {
            console.error("Broadcast message error:", error);
            this.reconnect();
        };
    },

    // Handle incoming messages
    handleMessage(type, data) {
        if (!type || !data) {
            console.warn("Invalid message format:", { type, data });
            return;
        }

        try {
            switch (type) {
                case "progress_update":
                    // Handle both data structures (direct and nested)
                    const progressData = {
                        percentage: parseFloat(
                            data.percentage || data.percent || 0
                        ),
                        progress: {
                            completed: parseInt(
                                data.progress?.completed || data.completed || 0
                            ),
                            total: parseInt(
                                data.progress?.total || data.total || 0
                            ),
                            message:
                                data.progress?.message ||
                                data.message ||
                                "Processing...",
                            status:
                                data.progress?.status ||
                                data.status ||
                                "running",
                        },
                    };

                    // Validate numeric values
                    if (
                        isNaN(progressData.percentage) ||
                        isNaN(progressData.progress.completed) ||
                        isNaN(progressData.progress.total)
                    ) {
                        console.warn(
                            "Invalid progress numeric values:",
                            progressData
                        );
                        return;
                    }

                    // Store latest progress
                    localStorage.setItem(
                        "translationProgress",
                        JSON.stringify(progressData)
                    );

                    // Emit custom event for progress update
                    window.dispatchEvent(
                        new CustomEvent("translation_progress_update", {
                            detail: progressData,
                        })
                    );
                    break;

                case "translation_complete":
                    // Clean up stored progress
                    localStorage.removeItem("translationProgress");
                    localStorage.removeItem("translationInProgress");

                    // Emit custom event for translation complete
                    window.dispatchEvent(
                        new CustomEvent("translation_complete", {
                            detail: data,
                        })
                    );
                    break;

                default:
                    console.warn("Unknown broadcast message type:", type);
            }
        } catch (error) {
            console.error("Error dispatching event:", error);
        }
    },

    // Send a message to other tabs
    sendMessage(type, data) {
        if (!this.channel || !this.isInitialized) {
            console.warn("Broadcast channel not initialized");
            return;
        }

        try {
            // Validate message before sending
            if (!type || !data) {
                console.warn("Invalid message format:", { type, data });
                return;
            }

            this.channel.postMessage({ type, data });
        } catch (error) {
            console.error("Failed to send broadcast message:", error);
            this.reconnect();
        }
    },

    // Attempt to reconnect if connection fails
    reconnect() {
        console.log("Attempting to reconnect broadcast channel...");
        this.destroy();
        setTimeout(() => {
            this.init();
        }, 1000);
    },

    // Clean up when no longer needed
    destroy() {
        if (this.channel) {
            try {
                this.channel.close();
            } catch (error) {
                console.error("Error closing broadcast channel:", error);
            }
            this.channel = null;
        }
        this.isInitialized = false;
    },
};

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
    TranslationBroadcast.init();
});

// Handle page visibility changes
document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") {
        TranslationBroadcast.init();
    }
});

// Export for use in other files
window.TranslationBroadcast = TranslationBroadcast;
