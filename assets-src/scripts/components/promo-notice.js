/**
 * Admin UI - Promo Notice Component
 *
 * Requires: namespace.js, config.js, helpers.js
 */
(function (window, document) {
    'use strict';

    const PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) return;

    const PSA = window[PREFIX_CONFIG.namespace];
    if (!PSA) return;

    const Helpers = PSA.Helpers;
    const Config = PSA.Config;
    if (!Helpers || !Config) return;

    const PromoNotice = {

        /* ------------------------------------------------------------
         * Init
         * ------------------------------------------------------------ */
        init: function () {
            this.bindEvents();
        },

        /* ------------------------------------------------------------
         * Bind Events
         * ------------------------------------------------------------ */
        bindEvents: function () {

            document.addEventListener('click', (e) => {
                const target = Helpers.getElement(e.target);
                if (!target) return;

                const btn = target.closest(
                    PSA.dataSelector('action', 'dismiss-promo')
                );
                if (!btn) return;

                e.preventDefault();
                this.dismiss(btn);
            });
        },

        /* ------------------------------------------------------------
         * Restore card (if failed)
         * ------------------------------------------------------------ */
        restore: function (card, btn) {
            card.style.opacity = '1';
            card.style.transform = 'none';
            btn.disabled = false;
        },

        /* ------------------------------------------------------------
         * Dismiss card with animation + AJAX
         * ------------------------------------------------------------ */
        dismiss: function (btn) {

            const card = btn.closest(PSA.selector('promo-card'));
            if (!card || typeof window.fetch !== 'function') return;

            const ajaxUrl = Config.getAjaxUrl();
            const nonce = Config.getNonce();
            if (!ajaxUrl || !nonce) return;

            btn.disabled = true;

            /* Animation */
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(-10px)';

            /* Prepare AJAX */
            const formData = new FormData();
            formData.append('action', PSA.ajaxAction('dismiss_promo_notice'));
            formData.append('security', nonce);

            window.fetch(ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
                .then((r) => r.json())
                .then((data) => {

                    if (data && data.success) {
                        setTimeout(() => {
                            if (card.parentNode) card.parentNode.removeChild(card);
                        }, 300);
                    } else {
                        this.restore(card, btn);
                    }
                })
                .catch(() => {
                    this.restore(card, btn);
                });
        }
    };

    /* ------------------------------------------------------------
     * Init Component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            PromoNotice.init();
        });
    } else {
        PromoNotice.init();
    }

    PSA.PromoNotice = PromoNotice;

})(window, document);