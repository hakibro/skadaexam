const isStandalone = () =>
    window.matchMedia('(display-mode: standalone)').matches ||
    window.navigator.standalone === true;

const isDesktopViewport = () =>
    window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 1024px)').matches;

const isFullscreenLike = () => {
    if (document.fullscreenElement) {
        return true;
    }

    const heightDiff = Math.abs(window.innerHeight - screen.height);
    const availHeightDiff = Math.abs(window.innerHeight - screen.availHeight);

    return heightDiff <= 2 || availHeightDiff <= 2;
};

const shouldGateExam = () =>
    !isStandalone() && !(isDesktopViewport() && isFullscreenLike());

let deferredInstallPrompt = null;

window.SkadaExamPwa = {
    isStandalone,
    isFullscreenLike,
    shouldGateExam,
    canPromptInstall: () => Boolean(deferredInstallPrompt),
    promptInstall: async () => {
        if (!deferredInstallPrompt) {
            return { outcome: 'unavailable' };
        }

        deferredInstallPrompt.prompt();
        const choice = await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        return choice;
    },
};

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstallPrompt = event;
    window.dispatchEvent(new CustomEvent('skadaexam:pwa-install-available'));
});

function showPwaGate(targetUrl = null) {
    const isRequiredPage = document.body?.dataset.requirePwa === '1';
    if (!shouldGateExam()) {
        return;
    }

    let gate = document.getElementById('pwa-required-gate');
    if (!gate) {
        gate = document.createElement('div');
        gate.id = 'pwa-required-gate';
        gate.className = 'fixed inset-0 bg-slate-950/90 text-white flex items-center justify-center p-4';
        gate.style.zIndex = '2147483647';
        gate.innerHTML = `
            <div class="w-full max-w-lg rounded-xl bg-white text-slate-900 shadow-2xl p-6">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                        <i class="fas fa-mobile-screen-button"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold mb-2">Buka Ujian dari Aplikasi</h2>
                        <p class="text-sm text-slate-600 mb-4">
                            Ujian hanya bisa dimulai dari mode PWA/installed app agar tampilan lebih stabil selama pengerjaan.
                        </p>
                        <div class="space-y-2 text-sm text-slate-700">
                            <p><strong>Android/Chrome:</strong> tekan tombol Install jika tersedia, lalu buka SkadaExam dari ikon aplikasi.</p>
                            <p><strong>iPhone/iPad:</strong> buka menu Share, pilih Add to Home Screen, lalu buka dari ikon SkadaExam.</p>
                            <p><strong>Laptop Chrome/Edge:</strong> gunakan ikon Install di address bar jika tersedia.</p>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <button type="button" data-pwa-install
                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                                Install Aplikasi
                            </button>
                            <button type="button" data-pwa-close
                                class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">
                                Nanti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    document.body.appendChild(gate);
    gate.style.zIndex = '2147483647';
    gate.classList.remove('hidden');
    gate.dataset.targetUrl = targetUrl || '';

    const installButton = gate.querySelector('[data-pwa-install]');
    if (installButton) {
        installButton.disabled = !deferredInstallPrompt;
        installButton.classList.toggle('opacity-50', !deferredInstallPrompt);
        installButton.classList.toggle('cursor-not-allowed', !deferredInstallPrompt);
        installButton.onclick = async () => {
            const choice = await window.SkadaExamPwa.promptInstall();
            if (choice.outcome === 'accepted') {
                installButton.textContent = 'Terpasang. Buka dari ikon aplikasi.';
            }
        };
    }

    const closeButton = gate.querySelector('[data-pwa-close]');
    if (closeButton) {
        closeButton.classList.toggle('hidden', isRequiredPage);
        closeButton.onclick = () => {
            if (!isRequiredPage) {
                gate.classList.add('hidden');
            }
        };
    }
}

window.SkadaExamPwa.showGate = showPwaGate;

document.addEventListener('click', event => {
    const link = event.target.closest('a[data-require-pwa="1"]');
    if (!link || !shouldGateExam()) {
        return;
    }

    event.preventDefault();
    showPwaGate(link.href);
});

document.addEventListener('DOMContentLoaded', () => {
    if (document.body?.dataset.requirePwa === '1' && shouldGateExam()) {
        showPwaGate(window.location.href);
    }
});
