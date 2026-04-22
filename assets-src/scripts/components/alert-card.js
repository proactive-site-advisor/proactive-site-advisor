/**
 * Admin UI - Alert Card Component
 *
 * Handles expand/collapse functionality for alert cards with details
 * Requires: namespace.js, helpers.js
 */
(function (window, document) {
    'use strict';

    const PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) return;

    const PSA = window[PREFIX_CONFIG.namespace];
    if (!PSA) return;

    const Helpers = PSA.Helpers;
    if (!Helpers) return;

    const AlertCard = {

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

                /* Toggle button click */
                const toggle = target.closest(PSA.selector('alert-card__toggle'));
                if (toggle) {
                    const card = toggle.closest(PSA.selector('alert-card--collapsible'));
                    if (card) this.toggleCard(card);
                    return;
                }

                /* Card body click (not interactive elements) */
                const body = target.closest(PSA.selector('alert-card__body'));
                if (!body) return;

                if (target.closest('a, button, input, select, textarea')) return;

                const card = body.closest(PSA.selector('alert-card--collapsible'));
                if (card) this.toggleCard(card);
            });
        },

        /* ------------------------------------------------------------
         * Toggle card open/close
         * ------------------------------------------------------------ */
        toggleCard: function (card) {

            const toggle = card.querySelector(PSA.selector('alert-card__toggle'));
            const details = card.querySelector(PSA.selector('alert-card__details'));
            if (!toggle || !details) return;

            const expanded = toggle.getAttribute('aria-expanded') === 'true';

            toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            details.hidden = expanded;

            card.classList.toggle(
                PSA.cssClass('alert-card--expanded'),
                !expanded
            );
        }
    };

    /* ------------------------------------------------------------
     * Init Component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            AlertCard.init();
        });
    } else {
        AlertCard.init();
    }

    PSA.AlertCard = AlertCard;

})(window, document);