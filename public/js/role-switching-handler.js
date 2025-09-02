/**
 * Role Switching Handler
 * Fixes issues when switching between different roles
 */

(function () {
    document.addEventListener("DOMContentLoaded", function () {
        console.log("[Role Switching] Initializing role switching handler");

        // Check if we have a warning message about role access
        const warningMessage = document.querySelector(
            ".bg-yellow-100.border-l-4.border-yellow-500"
        );

        if (warningMessage && warningMessage.textContent.includes("akses")) {
            console.log(
                "[Role Switching] Detected access warning after role switch"
            );

            // Detect which role links we need to fix
            fixRoleSwitchingLinks();
        }

        // Fix links that switch between roles
        function fixRoleSwitchingLinks() {
            // Find all role-related links in sidebar
            const roleLinks = [
                ...document.querySelectorAll('a[href*="/data/"]'),
                ...document.querySelectorAll('a[href*="/naskah/"]'),
                ...document.querySelectorAll('a[href*="/pengawas/"]'),
                ...document.querySelectorAll('a[href*="/koordinator/"]'),
                ...document.querySelectorAll('a[href*="/ruangan/"]'),
                ...document.querySelectorAll('a[href*="/admin/"]'),
            ];

            roleLinks.forEach((link) => {
                // Skip if already fixed
                if (link.getAttribute("data-role-fixed")) return;

                // Add click handler
                link.addEventListener("click", function (e) {
                    const href = this.getAttribute("href");
                    if (!href) return;

                    // Extract the role from the URL
                    const urlParts = href.split("/");
                    if (urlParts.length < 2) return;

                    const targetRole = urlParts[1]; // e.g., "data", "naskah"
                    console.log(
                        `[Role Switching] Navigating to ${targetRole} role`
                    );

                    // Show loading indicator
                    showPageLoader();

                    // Allow the navigation to proceed
                });

                // Mark as fixed
                link.setAttribute("data-role-fixed", "true");
            });
        }

        // Creates a full-page loader
        function showPageLoader() {
            // Check if loader already exists
            if (document.getElementById("page-role-switcher-loader")) return;

            const loader = document.createElement("div");
            loader.id = "page-role-switcher-loader";
            loader.className =
                "fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center";
            loader.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-3">
                    <div class="text-blue-600">
                        <i class="fa-solid fa-spinner fa-spin text-3xl"></i>
                    </div>
                    <span class="text-gray-700 font-medium">Berpindah role...</span>
                    <p class="text-gray-500 text-sm">Mohon tunggu sebentar</p>
                </div>
            `;

            document.body.appendChild(loader);
        }
    });
})();
