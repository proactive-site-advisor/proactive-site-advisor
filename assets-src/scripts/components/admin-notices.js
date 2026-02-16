/**
 * Admin UI - Admin Notices Component
 *
 * Handles dismissing notices via AJAX.
 * Requires: namespace.js, config.js, helpers.js
 */
(function (window, document) {
    'use strict';

    var PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) throw new Error('AdminNotices requires namespace.js (__PREFIX_CONFIG__).');

    var ProactiveSiteAdvisor = window[PREFIX_CONFIG.namespace];
    if (!ProactiveSiteAdvisor) throw new Error('AdminNotices requires global namespace.');

    var Helpers = ProactiveSiteAdvisor.Helpers;
    var Config = ProactiveSiteAdvisor.Config;
    if (!Helpers || !Config) throw new Error('AdminNotices requires helpers.js and config.js.');

    function dismissNotice(noticeId) {
        if (!noticeId || typeof window.fetch !== 'function') return;

        var ajaxUrl = Config.getAjaxUrl();
        var nonce = Config.getNonce();
        if (!ajaxUrl || !nonce) return;

        var formData = new FormData();
        formData.append('action', ProactiveSiteAdvisor.ajaxAction('dismiss_notice'));
        formData.append('notice_id', noticeId);
        formData.append('security', nonce);

        window.fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).catch(function () {
        });
    }

    function init() {
        document.addEventListener('click', function (e) {
            var target = Helpers.getElement(e.target);
            if (!target) return;

            var btn = target.closest(
                ProactiveSiteAdvisor.selector('dismissible-notice') + ' .notice-dismiss'
            );
            if (!btn) return;

            var notice = btn.closest(
                ProactiveSiteAdvisor.selector('notice')
            );
            if (!notice) return;

            var noticeId = notice.getAttribute(
                ProactiveSiteAdvisor.dataAttr('notice-id')
            );

            dismissNotice(noticeId);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    ProactiveSiteAdvisor.AdminNotices = {init: init};

})(window, document);
