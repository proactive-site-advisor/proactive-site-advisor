/**
 * Admin UI - Configuration
 *
 * Requires: namespace.js
 */
(function (window, document) {
    'use strict';

    const PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) return;

    const PSA = window[PREFIX_CONFIG.namespace];
    if (!PSA) return;

    const configObject = PREFIX_CONFIG.configObject;

    PSA.Config = {

        getCssVar: function (name) {
            return getComputedStyle(document.documentElement)
                .getPropertyValue(PSA.cssVar(name))
                .trim();
        },

        getColor: function (name) {
            const cfg = window[configObject] || {};
            return this.getCssVar(name) || (cfg.colors && cfg.colors[name]) || '';
        },

        getColors: function () {
            return {
                primary: this.getColor('primary'),
                secondary: this.getColor('secondary'),
                success: this.getColor('success'),
                info: this.getColor('info'),
                warning: this.getColor('warning'),
                error: this.getColor('error'),
                light: this.getColor('light'),
                dark: this.getColor('dark')
            };
        },

        getTheme: function () {
            const wrap = document.querySelector('.' + PSA.cssClass('wrap'));

            return (wrap && wrap.getAttribute(PSA.dataAttr('theme'))) ||
                document.documentElement.getAttribute(PSA.dataAttr('theme')) ||
                'light';
        },

        isDarkMode: function () {
            return this.getTheme() === 'dark';
        },

        getAjaxUrl: function () {
            const cfg = window[configObject] || {};
            return cfg.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php';
        },

        getNonce: function () {
            const cfg = window[configObject] || {};
            return cfg.nonce || '';
        },

        getPrefixConfig: function () {
            return PREFIX_CONFIG;
        },

        getI18n: function (key = null, fallback = '') {
            const cfg = window[configObject] || {};
            const i18n = cfg.i18n || {};

            if (key === null) {
                return i18n;
            }

            return key in i18n ? i18n[key] : (fallback || key);
        }
    };

})(window, document);