/**
 * Debug-Reload.js - Utility to detect and prevent unwanted page reloads
 * Updated version to fix double-click and auto-reload issues
 */

(function () {
    // Store original functions
    const originalSetInterval = window.setInterval;
    const originalSetTimeout = window.setTimeout;
    const originalReload = window.location.reload;
    const originalAssign = window.location.assign;
    const originalReplace = window.location.replace;

    // Track all intervals and timeouts
    const intervals = {};
    const timeouts = {};
    let intervalCounter = 0;
    let timeoutCounter = 0;

    // Track link clicks and navigation to prevent double navigation
    const navigationHistory = {};
    let navigationPending = false;

    // Replace setInterval
    window.setInterval = function (callback, delay) {
        const stack = new Error().stack;
        const id = originalSetInterval.apply(this, arguments);
        intervalCounter++;
        intervals[id] = {
            id: id,
            callback: callback.toString().substring(0, 100),
            delay: delay,
            stack: stack,
            counter: intervalCounter,
            timestamp: new Date(),
        };
        console.log(
            `[Debug] setInterval #${intervalCounter} created with ID ${id}, delay ${delay}ms`
        );
        return id;
    };

    // Replace setTimeout
    window.setTimeout = function (callback, delay) {
        const id = originalSetTimeout.apply(this, arguments);
        if (delay > 1000) {
            // Only track longer timeouts
            timeoutCounter++;
            timeouts[id] = {
                id: id,
                callback: callback.toString().substring(0, 100),
                delay: delay,
                counter: timeoutCounter,
                timestamp: new Date(),
            };
            console.log(
                `[Debug] setTimeout #${timeoutCounter} created with ID ${id}, delay ${delay}ms`
            );
        }
        return id;
    };

    // Replace reload
    window.location.reload = function () {
        console.warn(
            "[Debug] window.location.reload called",
            new Error().stack
        );

        // Check if navigation is already pending
        if (navigationPending) {
            console.log(
                "[Debug] Reload prevented - navigation already in progress"
            );
            return false;
        }

        navigationPending = true;

        // Allow the reload but prevent multiple calls
        console.log("[Debug] Allowing reload and preventing duplicates");

        // Clear all intervals before navigation
        for (const id in intervals) {
            clearInterval(parseInt(id));
        }

        return originalReload.apply(this, arguments);
    };

    // Replace assign
    window.location.assign = function (url) {
        console.warn("[Debug] window.location.assign called with URL:", url);

        // Check for duplicate navigation
        const now = Date.now();
        const key = `assign-${url}`;

        if (navigationHistory[key] && now - navigationHistory[key] < 1000) {
            console.log("[Debug] Duplicate navigation prevented to:", url);
            return false;
        }

        // Check if navigation is already pending
        if (navigationPending) {
            console.log(
                "[Debug] Navigation prevented - navigation already in progress"
            );
            return false;
        }

        navigationPending = true;
        navigationHistory[key] = now;

        // Clear all intervals before navigation
        for (const id in intervals) {
            clearInterval(parseInt(id));
        }

        console.log("[Debug] Allowing navigation to:", url);
        return originalAssign.apply(this, arguments);
    };

    // Replace replace
    window.location.replace = function (url) {
        console.warn("[Debug] window.location.replace called with URL:", url);

        // Check for duplicate navigation
        const now = Date.now();
        const key = `replace-${url}`;

        if (navigationHistory[key] && now - navigationHistory[key] < 1000) {
            console.log(
                "[Debug] Duplicate navigation replacement prevented to:",
                url
            );
            return false;
        }

        // Check if navigation is already pending
        if (navigationPending) {
            console.log(
                "[Debug] Navigation replacement prevented - navigation already in progress"
            );
            return false;
        }

        navigationPending = true;
        navigationHistory[key] = now;

        // Clear all intervals before navigation
        for (const id in intervals) {
            clearInterval(parseInt(id));
        }

        console.log("[Debug] Allowing navigation replacement to:", url);
        return originalReplace.apply(this, arguments);
    };

    // Expose debug methods to window
    window.debugReload = {
        listIntervals: function () {
            console.table(intervals);
        },
        listTimeouts: function () {
            console.table(timeouts);
        },
        clearAllIntervals: function () {
            for (const id in intervals) {
                clearInterval(id);
                console.log(
                    `[Debug] Cleared interval #${intervals[id].counter}`
                );
            }
        },
    };

    console.log(
        "[Debug] Reload detector initialized. Use window.debugReload methods in console to debug."
    );
})();
