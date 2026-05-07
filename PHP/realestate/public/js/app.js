/**
 * NextGen Real Estate – Main JS
 * Vanilla JS (no framework dependency in Phase 1)
 */

'use strict';

// ── CSRF helper ──────────────────────────────────────────────────────── //
const CSRF = {
    token() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    },

    headers() {
        return {
            'X-CSRF-TOKEN': this.token(),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
    },
};

// ── HTTP helper ──────────────────────────────────────────────────────── //
const Http = {
    async get(url, params = {}) {
        const query = new URLSearchParams(params).toString();
        const res   = await fetch(query ? `${url}?${query}` : url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF.token() },
        });
        return res.json();
    },

    async post(url, data = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: CSRF.headers(),
            body: JSON.stringify(data),
        });
        return res.json();
    },

    async postForm(url, formData) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF.token(), 'Accept': 'application/json' },
            body: formData,
        });
        return res.json();
    },
};

// ── Flash message ────────────────────────────────────────────────────── //
const Flash = {
    show(type, message, duration = 4000) {
        const container = document.querySelector('.flash-container') || (() => {
            const el = document.createElement('div');
            el.className = 'flash-container container';
            document.body.insertBefore(el, document.querySelector('.main-content'));
            return el;
        })();

        const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
        const div = document.createElement('div');
        div.className = `flash flash--${type}`;
        div.innerHTML = `
            <i class="fa-solid ${icons[type] ?? 'fa-info-circle'}"></i>
            <span>${message}</span>
            <button class="flash__close" onclick="this.parentElement.remove()">×</button>
        `;
        container.appendChild(div);

        if (duration > 0) {
            setTimeout(() => div.remove(), duration);
        }
    },

    success(msg) { this.show('success', msg); },
    error(msg)   { this.show('error', msg); },
    warning(msg) { this.show('warning', msg); },
    info(msg)    { this.show('info', msg); },
};

// ── AJAX Search (Phase 4 will expand this) ───────────────────────────── //
const Search = {
    timer: null,

    init() {
        const input = document.querySelector('[data-live-search]');
        if (!input) return;

        input.addEventListener('input', (e) => {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.query(e.target.value), 300);
        });
    },

    async query(term) {
        if (term.length < 2) return;
        const results = await Http.get('/properties/search', { q: term, ajax: 1 });
        this.renderSuggestions(results);
    },

    renderSuggestions(data) {
        // Implemented in Phase 4
        console.log('Search results:', data);
    },
};

// ── Wishlist toggle ──────────────────────────────────────────────────── //
const Wishlist = {
    init() {
        document.querySelectorAll('[data-wishlist]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const id = btn.dataset.wishlist;
                const res = await Http.post('/wishlist/toggle', { property_id: id });
                if (res.status) {
                    btn.classList.toggle('active');
                    Flash.success(res.message);
                }
            });
        });
    },
};

// ── Init ─────────────────────────────────────────────────────────────── //
document.addEventListener('DOMContentLoaded', () => {
    Search.init();
    Wishlist.init();
});
