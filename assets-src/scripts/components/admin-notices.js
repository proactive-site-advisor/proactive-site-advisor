/**
 * Admin UI - Admin Notices Component
 *
 * Handles dismissing notices via AJAX.
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

    const AdminNotices = {

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

                const btn = target.closest(PSA.selector('notice') + ' ' + PSA.selector('notice-close'));
                if (!btn) return;

                const notice = btn.closest(PSA.selector('notice'));
                if (!notice) return;

                const noticeId = notice.getAttribute('id');

                this.dismissNotice(noticeId);
                notice.remove();
            });
        },

        /* ------------------------------------------------------------
         * AJAX Dismiss Notice
         * ------------------------------------------------------------ */
        dismissNotice: function (noticeId) {

            if (!noticeId || typeof window.fetch !== 'function') return;

            const ajaxUrl = Config.getAjaxUrl();
            const nonce = Config.getNonce();
            if (!ajaxUrl || !nonce) return;

            const formData = new FormData();
            formData.append('action', PSA.ajaxAction('dismiss_notice'));
            formData.append('notice_id', noticeId);
            formData.append('security', nonce);

            window.fetch(ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).catch(() => {
            });
        }
    };

    /* Init component */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            AdminNotices.init();
        });
    } else {
        AdminNotices.init();
    }

    PSA.AdminNotices = AdminNotices;

})(window, document);