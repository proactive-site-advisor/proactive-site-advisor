/**
 * Admin UI - Main Entry File
 *
 * Requires: namespace.js
 */
(function (window, document) {
    'use strict';

    var PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) throw new Error('App requires namespace.js (__PREFIX_CONFIG__).');

    var ProactiveSiteAdvisor = window[PREFIX_CONFIG.namespace];
    if (!ProactiveSiteAdvisor) throw new Error('App requires global namespace.');

    var App = {
        version: '1.0.0',
        initialized: false,

        init: function () {
            if (this.initialized) return;

            this.bindGlobalEvents();
            this.initialized = true;

            ProactiveSiteAdvisor.dispatch('ready', {version: this.version}, document);
        },

        bindGlobalEvents: function () {
            var self = this;

            document.addEventListener(
                ProactiveSiteAdvisor.event('contentLoaded'),
                function (e) {
                    self.initializeContainer(e.detail && e.detail.container);
                }
            );
        },

        initializeContainer: function (container) {
            container = container || document;

            if (
                ProactiveSiteAdvisor.AdminNotices &&
                typeof ProactiveSiteAdvisor.AdminNotices.init === 'function'
            ) {
                ProactiveSiteAdvisor.AdminNotices.init(container);
            }
        },

        contentLoaded: function (container) {
            ProactiveSiteAdvisor.dispatch('contentLoaded', {container: container}, document);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            App.init();
        });
    } else {
        App.init();
    }

    ProactiveSiteAdvisor.App = App;

})(window, document);