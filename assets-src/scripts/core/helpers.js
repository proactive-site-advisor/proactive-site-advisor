/**
 * Admin UI - Helper Functions
 *
 * Requires: namespace.js
 */

(function (window, document) {
    'use strict';

    const PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) return;

    const PSA = window[PREFIX_CONFIG.namespace];
    if (!PSA) return;

    const Helpers = {

        /* -----------------------------------------
         * Element Utilities
         * ----------------------------------------- */

        resolveEl: function (el) {
            if (!el) return null;
            return (typeof el === 'string') ? document.querySelector(el) : el;
        },

        isPlainObject: function (obj) {
            return Object.prototype.toString.call(obj) === '[object Object]';
        },

        getElement: function (t) {
            if (!t) return null;
            if (t.nodeType === 3) return t.parentElement;
            if (typeof t.closest !== 'function') return null;
            return t;
        },

        show: function (el) {
            el = Helpers.resolveEl(el);
            if (!el) return;

            el.classList.remove(PSA.cssClass('d-none'));
            el.classList.add(PSA.cssClass('d-block'));
        },

        hide: function (el) {
            el = Helpers.resolveEl(el);
            if (!el) return;

            el.classList.remove(PSA.cssClass('d-block'));
            el.classList.add(PSA.cssClass('d-none'));
        },

        toggle: function (el) {
            el = Helpers.resolveEl(el);
            if (!el) return;

            const hidden = PSA.cssClass('d-none');
            const block = PSA.cssClass('d-block');

            if (el.classList.contains(hidden)) {
                el.classList.remove(hidden);
                el.classList.add(block);
            } else {
                el.classList.remove(block);
                el.classList.add(hidden);
            }
        },

        addClass: function (el, cls) {
            el = Helpers.resolveEl(el);
            if (el && cls) el.classList.add(cls);
        },

        removeClass: function (el, cls) {
            el = Helpers.resolveEl(el);
            if (el && cls) el.classList.remove(cls);
        },

        hasClass: function (el, cls) {
            el = Helpers.resolveEl(el);
            return !!(el && cls && el.classList.contains(cls));
        },

        siblings: function (el) {
            el = Helpers.resolveEl(el);
            if (!el || !el.parentNode) return [];

            return Array.prototype.filter.call(el.parentNode.children, function (c) {
                return c !== el;
            });
        },


        /* -----------------------------------------
         * Timing Utilities
         * ----------------------------------------- */

        debounce: function (fn, wait, immediate) {
            let t;

            return function () {
                const ctx = this, args = arguments;

                const later = function () {
                    t = null;
                    if (!immediate) fn.apply(ctx, args);
                };

                const callNow = immediate && !t;

                clearTimeout(t);
                t = setTimeout(later, wait);

                if (callNow) fn.apply(ctx, args);
            };
        },

        throttle: function (fn, limit) {
            let inThrottle = false;

            return function () {
                if (inThrottle) return;

                fn.apply(this, arguments);
                inThrottle = true;

                setTimeout(function () {
                    inThrottle = false;
                }, limit);
            };
        },


        /* -----------------------------------------
         * General Utilities
         * ----------------------------------------- */

        formatNumber: function (num) {
            if (num === null || num === undefined) return '';
            return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        uniqueId: function (prefix) {
            prefix = prefix || PSA.cssClass('');

            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return prefix + window.crypto.randomUUID();
            }

            return (
                prefix +
                Date.now().toString(36) +
                Math.random().toString(36).slice(2)
            );
        },

        escapeHtml: function (text) {
            const div = document.createElement('div');
            div.textContent = (text === null || text === undefined) ? '' : String(text);
            return div.innerHTML;
        },

        merge: function (target, source) {
            target = target || {};
            if (!Helpers.isPlainObject(source)) return target;

            for (const k in source) {
                if (!Object.prototype.hasOwnProperty.call(source, k)) continue;

                let v = source[k];

                if (Helpers.isPlainObject(v)) {
                    target[k] = target[k] || {};
                    Helpers.merge(target[k], v);
                } else {
                    target[k] = v;
                }
            }
            return target;
        },


        /* -----------------------------------------
         * Storage Utilities
         * ----------------------------------------- */

        storageGet: function (key) {
            try {
                return window.localStorage.getItem(key);
            } catch (e) {
                return null;
            }
        },

        storageSet: function (key, value) {
            try {
                window.localStorage.setItem(key, value);
            } catch (e) {
            }
        }

    };

    /* -----------------------------------------
     * URL Helpers
     * ----------------------------------------- */
    Helpers.URL = {

        getParam: function (key) {
            return new URLSearchParams(window.location.search).get(key);
        },

        setParam: function (key, value) {
            const url = new URL(window.location.href);
            const params = url.searchParams;

            if (value === null || value === undefined || value === '') {
                params.delete(key);
            } else {
                params.set(key, value);
            }

            url.search = params.toString();
            window.location.href = url.toString();
        },

        removeParam: function (key) {
            const url = new URL(window.location.href);
            url.searchParams.delete(key);
            window.location.href = url.toString();
        },

        updateParams: function (obj) {
            const url = new URL(window.location.href);
            const params = url.searchParams;

            for (const key in obj) {
                const value = obj[key];

                if (value === null || value === undefined || value === '') {
                    params.delete(key);
                } else {
                    params.set(key, value);
                }
            }

            url.search = params.toString();
            window.location.href = url.toString();
        }
    };

    PSA.Helpers = Helpers;

})(window, document);