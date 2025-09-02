/**
 * Sidebar Navigation Fix
 * Prevents double-clicks and multiple navigation attempts
 */
document.addEventListener("DOMContentLoaded", function () {
    console.log("[Navigation Fix] Initializing sidebar navigation fix");

    // Track link clicks and navigation
    const clickHistory = {};

    // Fix navigation links to prevent double-click issues
    function fixNavigationLinks() {
        // Target sidebar navigation links
        const sidebarLinks = document.querySelectorAll("#sidebar a");

        sidebarLinks.forEach((link) => {
            // Skip links that already have event listeners
            if (link.getAttribute("data-navigation-fixed")) return;

            // Replace with our own click handler
            link.addEventListener("click", function (event) {
                const url = this.getAttribute("href");
                if (!url) return;

                // Skip non-URL links like # anchors
                if (url === "#") return;

                const now = Date.now();
                const key = `click-${url}`;

                // Prevent double clicks (clicks within 1 second)
                if (clickHistory[key] && now - clickHistory[key] < 1000) {
                    event.preventDefault();
                    console.log(
                        "[Navigation Fix] Prevented duplicate click to",
                        url
                    );
                    return false;
                }

                // Mark this link as being navigated to
                clickHistory[key] = now;

                // Add loading state to link
                this.classList.add("pointer-events-none", "opacity-75");
                const originalContent = this.innerHTML;
                const loadingSpinner = document.createElement("span");
                loadingSpinner.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin ml-2"></i>';
                this.appendChild(loadingSpinner);

                // Let the navigation happen naturally
                console.log("[Navigation Fix] Navigating to", url);
            });

            // Mark this link as fixed
            link.setAttribute("data-navigation-fixed", "true");
        });
    }

    // Fix the links initially
    fixNavigationLinks();

    // Also run periodically to catch dynamically added links
    setInterval(fixNavigationLinks, 2000);

    // Add global click handler for role switching links
    document.addEventListener("click", function (event) {
        // Check if this is a navigation between different roles
        const linkElement = event.target.closest("a");

        if (linkElement && linkElement.href) {
            // Check if this is a role-switching navigation
            const currentPath = window.location.pathname;
            const targetPath = new URL(linkElement.href, window.location.origin)
                .pathname;

            const currentRole = currentPath.split("/")[1]; // e.g. "data", "naskah"
            const targetRole = targetPath.split("/")[1]; // e.g. "data", "naskah"

            if (currentRole && targetRole && currentRole !== targetRole) {
                console.log(
                    `[Navigation Fix] Role switch detected: ${currentRole} → ${targetRole}`
                );

                // Clear any stored session or cache data that might cause redirect loops
                if (window.sessionStorage) {
                    console.log("[Navigation Fix] Clearing session storage");
                    sessionStorage.clear();
                }

                if (window.localStorage) {
                    // Only clear specific items related to navigation state
                    const keysToRemove = [];
                    for (let i = 0; i < localStorage.length; i++) {
                        const key = localStorage.key(i);
                        if (
                            key &&
                            (key.includes("redirect") ||
                                key.includes("navigation") ||
                                key.includes("state"))
                        ) {
                            keysToRemove.push(key);
                        }
                    }

                    keysToRemove.forEach((key) => {
                        console.log(
                            `[Navigation Fix] Removing localStorage item: ${key}`
                        );
                        localStorage.removeItem(key);
                    });
                }
            }
        }
    });
});
