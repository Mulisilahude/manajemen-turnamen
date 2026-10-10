import '@fontsource-variable/plus-jakarta-sans';
import '@fontsource-variable/sora';

const root = document.documentElement;
const STORAGE_KEY = 'theme';

function terapkanIkon() {
    const gelap = root.classList.contains('dark');

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', gelap ? 'true' : 'false');
        btn.setAttribute('title', gelap ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
        btn.setAttribute('aria-label', gelap ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    });

    document.querySelectorAll('[data-theme-icon="sun"]').forEach((el) => el.classList.toggle('hidden', !gelap));
    document.querySelectorAll('[data-theme-icon="moon"]').forEach((el) => el.classList.toggle('hidden', gelap));
}

function pasangTema(gelap, simpan = true) {
    root.classList.toggle('dark', gelap);

    if (simpan) {
        try {
            localStorage.setItem(STORAGE_KEY, gelap ? 'dark' : 'light');
        } catch (e) {
            /* localStorage tidak tersedia */
        }
    }

    terapkanIkon();
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');

    if (toggle) {
        event.preventDefault();
        pasangTema(!root.classList.contains('dark'));
        return;
    }

    const navToggle = event.target.closest('[data-nav-toggle]');

    if (navToggle) {
        event.preventDefault();
        const target = document.querySelector(navToggle.getAttribute('data-nav-toggle'));
        if (target) {
            target.classList.toggle('hidden');
        }
    }
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('details[open]').forEach((detail) => {
        if (!detail.contains(event.target)) {
            detail.removeAttribute('open');
        }
    });
});

terapkanIkon();

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        let tersimpan = null;
        try {
            tersimpan = localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            tersimpan = null;
        }

        if (!tersimpan) {
            pasangTema(event.matches, false);
        }
    });
}

const INTERVAL_SKOR = 20000;

function inisialisasiPengkinianSkor() {
    const papan = document.querySelector('[data-match-endpoint]');

    if (!papan) {
        return;
    }

    const endpoint = papan.getAttribute('data-match-endpoint') + window.location.search;

    let timer = null;

    const adaBerlangsung = () =>
        document.querySelector('[data-match-card][data-match-status="berlangsung"]') !== null;

    async function tarik() {
        try {
            const res = await fetch(endpoint, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });

            if (!res.ok) {
                return;
            }

            const data = await res.json();

            (data.matches || []).forEach((match) => {
                const kartu = document.querySelector('[data-match-card][data-match-id="' + match.id + '"]');

                if (!kartu) {
                    return;
                }

                kartu.setAttribute('data-match-status', match.status);

                kartu.querySelectorAll('[data-match-field="score"]').forEach((el) => {
                    el.textContent = match.skor;
                    el.classList.toggle('score-pill', match.ada_skor);
                    el.classList.toggle('score-pill-muted', !match.ada_skor);
                });

                kartu.querySelectorAll('[data-match-field="status"]').forEach((el) => {
                    el.textContent = match.label;
                    el.className = match.badge;
                });
            });
        } catch (e) {
            /* abaikan kegagalan jaringan sementara */
        }
    }

    function jadwalkan() {
        clearTimeout(timer);
        timer = null;

        if (document.hidden || !adaBerlangsung()) {
            return;
        }

        timer = setTimeout(async () => {
            await tarik();
            jadwalkan();
        }, INTERVAL_SKOR);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearTimeout(timer);
            timer = null;

            return;
        }

        if (!timer) {
            jadwalkan();
        }
    });

    jadwalkan();
}

inisialisasiPengkinianSkor();
