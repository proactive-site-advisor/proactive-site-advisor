/**
 * Admin UI - Header Component
 *
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

    const Header = {

        toggleBtn: null,
        nav: null,
        wrapper: null,
        isOpen: false,

        /* ------------------------------------------------------------
         * Init
         * ------------------------------------------------------------ */
        init: function () {

            this.toggleBtn = document.querySelector(PSA.selector('header-toggle'));
            this.nav = document.getElementById(PSA.cssClass('header-nav'));
            this.wrapper = document.querySelector(PSA.selector('header-nav-wrapper'));

            if (!this.toggleBtn || !this.nav) return;

            this.bindEvents();
        },

        /* ------------------------------------------------------------
         * Bind Events
         * ------------------------------------------------------------ */
        bindEvents: function () {

            /* Toggle button click */
            this.toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });

            /* Close on outside click */
            document.addEventListener('click', (e) => {

                if (!this.isOpen) return;

                const target = Helpers.getElement(e.target);

                const inside =
                    (this.wrapper && this.wrapper.contains(target)) ||
                    (this.toggleBtn && this.toggleBtn.contains(target));

                if (!inside) this.close();
            });

            /* ESC key closes menu */
            document.addEventListener('keydown', (e) => {

                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                    this.toggleBtn.focus();
                }
            });

            /* Clicking a nav link closes menu */
            this.nav.addEventListener('click', (e) => {
                const target = Helpers.getElement(e.target);

                if (target && target.closest(PSA.selector('header-nav-link'))) {
                    this.close();
                }
            });

            /* On resize close header on desktop */
            const onResize = Helpers.debounce(() => {
                if (window.innerWidth > 991.98 && this.isOpen) {
                    this.close();
                }
            }, 100);

            window.addEventListener('resize', onResize);
        },

        /* ------------------------------------------------------------
         * Toggle
         * ------------------------------------------------------------ */
        toggle: function () {
            this.isOpen ? this.close() : this.open();
        },

        /* ------------------------------------------------------------
         * Open navigation
         * ------------------------------------------------------------ */
        open: function () {

            this.toggleBtn.setAttribute('aria-expanded', 'true');

            this.nav.classList.add(PSA.cssClass('show'));
            this.isOpen = true;

            /* focus first focusable */
            const first = this.nav.querySelector(
                'a, button, [tabindex]:not([tabindex="-1"])'
            );
            if (first) first.focus();

        },

        /* ------------------------------------------------------------
         * Close navigation
         * ------------------------------------------------------------ */
        close: function () {

            this.toggleBtn.setAttribute('aria-expanded', 'false');

            this.nav.classList.remove(PSA.cssClass('show'));
            this.isOpen = false;

        }
    };

    /* ------------------------------------------------------------
     * Init Component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            Header.init();
        });
    } else {
        Header.init();
    }

    PSA.Header = Header;

})(window, document);