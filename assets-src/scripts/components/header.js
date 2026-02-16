/**
 * Admin UI - Header Component
 *
 * Requires: namespace.js, helpers.js
 */
(function (window, document) {
    'use strict';

    var PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) throw new Error('Header requires namespace.js (__PREFIX_CONFIG__).');

    var ProactiveSiteAdvisor = window[PREFIX_CONFIG.namespace];
    if (!ProactiveSiteAdvisor) throw new Error('Header requires global namespace.');

    var Helpers = ProactiveSiteAdvisor.Helpers;
    if (!Helpers) throw new Error('Header requires helpers.js.');

    var Header = {
        toggleBtn: null,
        nav: null,
        wrapper: null,
        isOpen: false,

        init: function () {
            this.toggleBtn = document.querySelector(
                ProactiveSiteAdvisor.selector('header-toggle')
            );
            this.nav = document.querySelector(
                ProactiveSiteAdvisor.selector('header-nav')
            );
            this.wrapper = document.querySelector(
                ProactiveSiteAdvisor.selector('header-nav-wrapper')
            );

            if (!this.toggleBtn || !this.nav) return;

            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            this.toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggle();
            });

            document.addEventListener('click', function (e) {
                if (!self.isOpen) return;

                var target = Helpers.getElement(e.target) || e.target;

                var inside =
                    (self.wrapper && self.wrapper.contains(target)) ||
                    (self.toggleBtn && self.toggleBtn.contains(target));

                if (!inside) self.close();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && self.isOpen) {
                    self.close();
                    self.toggleBtn.focus();
                }
            });

            this.nav.addEventListener('click', function (e) {
                var target = Helpers.getElement(e.target) || e.target;

                if (target && target.closest(
                    ProactiveSiteAdvisor.selector('header-nav-link')
                )) {
                    self.close();
                }
            });

            var onResize = Helpers.debounce(function () {
                if (window.innerWidth > 991.98 && self.isOpen) {
                    self.close();
                }
            }, 100);

            window.addEventListener('resize', onResize);
        },

        toggle: function () {
            this.isOpen ? this.close() : this.open();
        },

        open: function () {
            this.toggleBtn.setAttribute('aria-expanded', 'true');
            this.nav.classList.add(
                ProactiveSiteAdvisor.cssClass('show')
            );
            this.isOpen = true;

            var first = this.nav.querySelector(
                'a, button, [tabindex]:not([tabindex="-1"])'
            );
            if (first) first.focus();

            ProactiveSiteAdvisor.dispatch('header:opened', {nav: this.nav}, document);
        },

        close: function () {
            this.toggleBtn.setAttribute('aria-expanded', 'false');
            this.nav.classList.remove(
                ProactiveSiteAdvisor.cssClass('show')
            );
            this.isOpen = false;

            ProactiveSiteAdvisor.dispatch('header:closed', {nav: this.nav}, document);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            Header.init();
        });
    } else {
        Header.init();
    }

    ProactiveSiteAdvisor.Header = Header;

})(window, document);
