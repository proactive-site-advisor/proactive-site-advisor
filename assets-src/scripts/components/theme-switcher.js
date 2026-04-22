/**
 * Admin UI - Theme Switcher
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

    const ThemeSwitcher = {

        wrapper: null,
        storageKey: null,
        toggleBtn: null,

        /* ------------------------------------------------------------
         * Init
         * ------------------------------------------------------------ */
        init: function () {

            this.storageKey = PSA.storageKey('theme');
            this.wrapper = document.querySelector(PSA.selector('ui'));
            this.toggleBtn = document.getElementById(PSA.cssClass('theme-toggle'));

            this.bindEvents();
            this.applyStoredTheme();
        },

        /* ------------------------------------------------------------
         * Events
         * ------------------------------------------------------------ */
        bindEvents: function () {

            if (this.toggleBtn) {
                this.toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggle();
                });
            }

        },

        /* ------------------------------------------------------------
         * Theme Logic
         * ------------------------------------------------------------ */
        getTheme: function () {

            const stored = Helpers.storageGet(this.storageKey);
            if (stored === 'light' || stored === 'dark') {
                return stored;
            }

            if (this.wrapper) {
                if (this.wrapper.classList.contains(PSA.cssClass('theme-light'))) return 'light';
                if (this.wrapper.classList.contains(PSA.cssClass('theme-dark'))) return 'dark';
            }

            return 'light';
        },

        setTheme: function (theme) {

            if (theme !== 'light' && theme !== 'dark') {
                theme = 'light';
            }

            if (this.wrapper) {
                this.wrapper.classList.remove(PSA.cssClass('theme-light'), PSA.cssClass('theme-dark'));
                this.wrapper.classList.add(PSA.cssClass(`theme-${theme}`));
            }

            Helpers.storageSet(this.storageKey, theme);

            this.saveToServer(theme);

        },

        toggle: function () {
            const nextTheme = this.getTheme() === 'light' ? 'dark' : 'light';
            this.setTheme(nextTheme);
        },

        /* ------------------------------------------------------------
         * Apply stored theme on load
         * ------------------------------------------------------------ */
        applyStoredTheme: function () {

            const stored = Helpers.storageGet(this.storageKey);
            const theme = (stored === 'light' || stored === 'dark')
                ? stored
                : this.getTheme();

            if (stored && this.wrapper) {
                this.wrapper.classList.remove(PSA.cssClass('theme-light'), PSA.cssClass('theme-dark'));
                this.wrapper.classList.add(PSA.cssClass(`theme-${theme}`));
            }

        },

        /* ------------------------------------------------------------
         * Save Theme to Server
         * ------------------------------------------------------------ */
        saveToServer: function (theme) {

            if (typeof window.fetch !== 'function') return;

            const ajaxUrl = Config.getAjaxUrl();
            const nonce = Config.getNonce();
            if (!ajaxUrl || !nonce) return;

            const formData = new FormData();
            formData.append('action', PSA.ajaxAction('switch_theme'));
            formData.append('security', nonce);
            formData.append('theme', theme);

            window.fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).catch(() => {
            });
        }
    };

    /* ------------------------------------------------------------
     * Init component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            ThemeSwitcher.init();
        });
    } else {
        ThemeSwitcher.init();
    }

    PSA.ThemeSwitcher = ThemeSwitcher;

})(window, document);