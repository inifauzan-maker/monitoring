import '@tabler/core/dist/js/tabler.esm.js';

const ATTR_TEMA = 'data-bs-theme';
const KUNCI_TEMA = 'tema_aplikasi';

const terapkanTema = (tema) => {
    document.documentElement.setAttribute(ATTR_TEMA, tema);
    localStorage.setItem(KUNCI_TEMA, tema);

    document.querySelectorAll('[data-aksi="ubah-tema"]').forEach((tombol) => {
        tombol.querySelector('.ikon-mode-gelap')?.classList.toggle('d-none', tema === 'dark');
        tombol.querySelector('.ikon-mode-terang')?.classList.toggle('d-none', tema !== 'dark');
    });
};

const inisialisasiTema = () => {
    const temaAktif = document.documentElement.getAttribute(ATTR_TEMA) ?? 'light';

    terapkanTema(temaAktif);

    document.querySelectorAll('[data-aksi="ubah-tema"]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            const temaBerikutnya = document.documentElement.getAttribute(ATTR_TEMA) === 'dark' ? 'light' : 'dark';

            terapkanTema(temaBerikutnya);
        });
    });
};

const inisialisasiSalinTeks = () => {
    document.addEventListener('click', async (event) => {
        const tombol = event.target.closest('[data-copy-text]');

        if (! tombol) {
            return;
        }

        const teksAwal = tombol.textContent;
        const nilai = tombol.dataset.copyText ?? '';

        try {
            await navigator.clipboard.writeText(nilai);
            tombol.textContent = 'Tersalin';
        } catch {
            tombol.textContent = 'Gagal';
        }

        window.setTimeout(() => {
            tombol.textContent = teksAwal;
        }, 1400);
    });
};

const inisialisasiFokusEditorLink = () => {
    let editorAktif = null;
    let timerBersih = null;
    const kelasAktif = ['border-primary', 'bg-primary-lt', 'shadow-sm'];

    const bersihkanEditorAktif = () => {
        if (timerBersih) {
            window.clearTimeout(timerBersih);
            timerBersih = null;
        }

        if (! editorAktif) {
            return;
        }

        editorAktif.classList.remove(...kelasAktif);
        editorAktif = null;
    };

    const aktifkanEditor = (targetId) => {
        const editor = document.getElementById(targetId);

        if (! editor || ! editor.matches('[data-link-editor]')) {
            return;
        }

        bersihkanEditorAktif();

        editorAktif = editor;
        editorAktif.classList.add(...kelasAktif);
        editorAktif.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });

        const inputUrl = editorAktif.querySelector('[data-link-url-input]');

        window.setTimeout(() => {
            inputUrl?.focus({ preventScroll: true });
            inputUrl?.select();
        }, 220);

        timerBersih = window.setTimeout(() => {
            bersihkanEditorAktif();
        }, 2600);
    };

    document.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-link-editor-trigger]');

        if (! tombol) {
            return;
        }

        event.preventDefault();

        const hash = tombol.getAttribute('href');

        if (! hash || ! hash.startsWith('#')) {
            return;
        }

        if (window.location.hash !== hash) {
            window.history.replaceState(null, '', hash);
        }

        aktifkanEditor(hash.slice(1));
    });

    if (window.location.hash.startsWith('#editor-link-')) {
        window.setTimeout(() => {
            aktifkanEditor(window.location.hash.slice(1));
        }, 180);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        inisialisasiTema();
        inisialisasiSalinTeks();
        inisialisasiFokusEditorLink();
    }, { once: true });
} else {
    inisialisasiTema();
    inisialisasiSalinTeks();
    inisialisasiFokusEditorLink();
}
