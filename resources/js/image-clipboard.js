/**
 * Image Clipboard Handler for SkadaExam
 * Handles clipboard paste functionality for image uploads
 */

class ImageClipboardHandler {
    /**
     * Initialize the image clipboard handler
     * @param {Object} config - Configuration options
     */
    constructor(config = {}) {
        this.config = Object.assign(
            {
                debug: false,
                maxSize: 5 * 1024 * 1024, // 5MB default
                validTypes: [
                    "image/jpeg",
                    "image/png",
                    "image/gif",
                    "image/webp",
                ],
                successClass: "paste-success",
                successTimeout: 1000,
                feedbackDuration: 2000,
            },
            config
        );

        // Set up global paste event
        document.addEventListener("paste", this.handleGlobalPaste.bind(this));

        if (this.config.debug) {
            console.log(
                "ImageClipboardHandler initialized with config:",
                this.config
            );
        }
    }

    /**
     * Handle paste event globally
     * @param {ClipboardEvent} e - The paste event
     */
    handleGlobalPaste(e) {
        // Find which drop zone is active/focused
        const activeElement = document.activeElement;
        let targetDropZone = null;

        // Check if we're focused in a textarea or inside a drop zone
        if (activeElement && activeElement.tagName === "TEXTAREA") {
            // If in textarea, don't interfere with normal paste
            return;
        }

        // Try to find appropriate target
        if (activeElement.classList.contains("image-drop-zone")) {
            // If directly focused on drop zone
            targetDropZone = activeElement;
        } else if (activeElement.closest(".image-drop-zone")) {
            // If focused on an element inside a drop zone
            targetDropZone = activeElement.closest(".image-drop-zone");
        } else {
            // Try to find which image drop zone is visible
            const visibleDropZones = Array.from(
                document.querySelectorAll(".image-drop-zone:not(.hidden)")
            );

            if (visibleDropZones.length === 1) {
                // If only one visible drop zone, use that
                targetDropZone = visibleDropZones[0];
            }
        }

        // If we found a target drop zone, process the paste
        if (
            targetDropZone &&
            targetDropZone.querySelector('input[type="file"]')
        ) {
            this.processPasteToDropZone(e, targetDropZone);
        }
    }

    /**
     * Process a paste event to a specific drop zone
     * @param {ClipboardEvent} e - The paste event
     * @param {HTMLElement} dropZone - The target drop zone
     */
    processPasteToDropZone(e, dropZone) {
        e.preventDefault();
        e.stopPropagation();

        // Get image from clipboard
        const clipboardData = e.clipboardData || window.clipboardData;
        const items = clipboardData.items;

        if (!items) return;

        // Find image in clipboard data
        let blob = null;
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") !== -1) {
                blob = items[i].getAsFile();
                break;
            }
        }

        // Process image if found
        if (blob) {
            const fileInput = dropZone.querySelector('input[type="file"]');
            const previewContainer = dropZone
                .closest(".form-group")
                .querySelector(".image-preview");
            const previewImg = previewContainer
                ? previewContainer.querySelector("img")
                : null;

            // Validate file
            if (
                !this.validateFile(
                    blob,
                    fileInput.getAttribute("data-max-size") ||
                        this.config.maxSize
                )
            ) {
                return;
            }

            try {
                // Create a new File object from the blob
                // This is more reliable than just using the blob directly
                const file = new File(
                    [blob],
                    `pasted-image-${Date.now()}.${this.getExtensionForMimeType(
                        blob.type
                    )}`,
                    {
                        type: blob.type,
                        lastModified: new Date().getTime(),
                    }
                );

                // Create a proper FileList-like object
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);

                // Set the file input's files property
                fileInput.files = dataTransfer.files;

                // Trigger change event to update any listeners
                fileInput.dispatchEvent(new Event("change", { bubbles: true }));

                // Show preview if available
                if (previewImg) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImg.src = e.target.result;
                        previewContainer.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                }

                // Visual feedback
                this.showPasteSuccess(dropZone);
            } catch (error) {
                console.error("Error processing pasted image:", error);
                this.showPasteError(
                    dropZone,
                    "Failed to process image. Please try uploading directly."
                );
            }
        }
    }

    /**
     * Validate a file against size and type constraints
     * @param {File|Blob} file - The file to validate
     * @param {Number} maxSize - Maximum file size in bytes
     * @returns {Boolean} - Whether the file is valid
     */
    validateFile(file, maxSize) {
        // Check file type
        if (!this.config.validTypes.includes(file.type)) {
            alert("Please select a valid image file (JPG, PNG, GIF, WebP)");
            return false;
        }

        // Check file size
        if (file.size > maxSize) {
            const maxSizeMB = Math.round((maxSize / (1024 * 1024)) * 10) / 10;
            alert(`File size must be less than ${maxSizeMB}MB`);
            return false;
        }

        return true;
    }

    /**
     * Show success feedback on paste
     * @param {HTMLElement} dropZone - The drop zone element
     */
    showPasteSuccess(dropZone) {
        // Add success class for animation
        dropZone.classList.add(this.config.successClass);
        setTimeout(() => {
            dropZone.classList.remove(this.config.successClass);
        }, this.config.successTimeout);

        // Show feedback message
        const feedbackMsg = document.createElement("div");
        feedbackMsg.className =
            "text-green-600 text-sm mt-2 absolute bottom-2 left-0 right-0 text-center";
        feedbackMsg.innerHTML =
            '<i class="fa-solid fa-check-circle mr-1"></i> Gambar berhasil ditempel!';

        // Make sure the dropzone has position relative
        if (window.getComputedStyle(dropZone).position === "static") {
            dropZone.style.position = "relative";
        }

        dropZone.appendChild(feedbackMsg);

        setTimeout(() => {
            if (dropZone.contains(feedbackMsg)) {
                dropZone.removeChild(feedbackMsg);
            }
        }, this.config.feedbackDuration);
    }

    /**
     * Show error feedback on paste
     * @param {HTMLElement} dropZone - The drop zone element
     * @param {String} message - The error message
     */
    showPasteError(dropZone, message) {
        const feedbackMsg = document.createElement("div");
        feedbackMsg.className =
            "text-red-600 text-sm mt-2 absolute bottom-2 left-0 right-0 text-center";
        feedbackMsg.innerHTML = `<i class="fa-solid fa-exclamation-circle mr-1"></i> ${message}`;

        if (window.getComputedStyle(dropZone).position === "static") {
            dropZone.style.position = "relative";
        }

        dropZone.appendChild(feedbackMsg);

        setTimeout(() => {
            if (dropZone.contains(feedbackMsg)) {
                dropZone.removeChild(feedbackMsg);
            }
        }, this.config.feedbackDuration);
    }

    /**
     * Get file extension from mime type
     * @param {String} mimeType - The mime type
     * @returns {String} - The file extension
     */
    getExtensionForMimeType(mimeType) {
        const mimeToExt = {
            "image/jpeg": "jpg",
            "image/jpg": "jpg",
            "image/png": "png",
            "image/gif": "gif",
            "image/webp": "webp",
        };

        return mimeToExt[mimeType] || "png";
    }
}

// Export the class
window.ImageClipboardHandler = ImageClipboardHandler;
