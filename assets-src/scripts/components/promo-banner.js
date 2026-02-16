/**
 * Promo Banner Component
 *
 * Requires: namespace.js, config.js, helpers.js
 */
(function (window, document) {
    'use strict';

    var PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) throw new Error('PromoBanner requires namespace.js (__PREFIX_CONFIG__).');

    var ProactiveSiteAdvisor = window[PREFIX_CONFIG.namespace];
    if (!ProactiveSiteAdvisor) throw new Error('PromoBanner requires global namespace.');

    var Helpers = ProactiveSiteAdvisor.Helpers;
    var Config = ProactiveSiteAdvisor.Config;
    if (!Helpers || !Config) throw new Error('PromoBanner requires helpers.js and config.js.');

    function restore(card, btn) {
        card.style.opacity = '1';
        card.style.transform = 'none';
        btn.disabled = false;
    }

    function dismiss(btn) {
        var card = btn.closest(
            ProactiveSiteAdvisor.selector('promo-card')
        );
        if (!card || typeof window.fetch !== 'function') return;

        var ajaxUrl = Config.getAjaxUrl();
        var nonce = Config.getNonce();
        if (!ajaxUrl || !nonce) return;

        btn.disabled = true;

        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';

        var formData = new FormData();
        formData.append(
            'action',
            ProactiveSiteAdvisor.ajaxAction('dismiss_promo_banner')
        );
        formData.append('security', nonce);

        window.fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data && data.success) {
                    setTimeout(function () {
                        if (card.parentNode) {
                            card.parentNode.removeChild(card);
                        }
                    }, 300);
                } else {
                    restore(card, btn);
                }
            })
            .catch(function () {
                restore(card, btn);
            });
    }

    function init() {
        document.addEventListener('click', function (e) {
            var target = Helpers.getElement(e.target);
            if (!target) return;

            var btn = target.closest(
                ProactiveSiteAdvisor.dataSelector('action', 'dismiss-promo')
            );
            if (!btn) return;

            e.preventDefault();
            dismiss(btn);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    ProactiveSiteAdvisor.PromoBanner = {init: init};

})(window, document);
