/**
 * Admin UI - Tabs Component
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

    const Tabs = {

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

                const tab = target.closest('[' + PSA.dataAttr('toggle') + '="tab"]');
                if (tab) {
                    e.preventDefault();
                    Tabs.show(tab);
                }
            });
        },

        /* ------------------------------------------------------------
         * Show a tab
         * ------------------------------------------------------------ */
        show: function (tab) {

            if (!tab) return;

            const tabList = tab.closest(
                PSA.selector('nav-tabs') + ', ' + PSA.selector('tab-nav-pills')
            );

            const targetId = tab.getAttribute(PSA.dataAttr('target')) || tab.getAttribute('href');
            if (!targetId) return;

            // Deactivate all tabs in the same list
            if (tabList) {
                tabList.querySelectorAll('[' + PSA.dataAttr('toggle') + '="tab"]').forEach((t) => {
                    t.classList.remove(PSA.cssClass('active'));
                    t.setAttribute('aria-selected', 'false');
                });
            }

            // Activate clicked tab
            tab.classList.add(PSA.cssClass('active'));
            tab.setAttribute('aria-selected', 'true');

            // Find tab content container
            const tabContent = document.querySelector(targetId);
            if (!tabContent) return;

            const tabPaneContainer =
                tabContent.closest(PSA.selector('tab-content')) || tabContent.parentElement;

            // Hide all panes in container
            if (tabPaneContainer) {
                tabPaneContainer.querySelectorAll(PSA.selector('tab-pane')).forEach((pane) => {
                    pane.classList.remove(PSA.cssClass('show'), PSA.cssClass('active'));
                });
            }

            // Show target pane
            tabContent.classList.add(PSA.cssClass('show'), PSA.cssClass('active'));
        },

        /* ------------------------------------------------------------
         * Get active tab inside a list (string selector or element)
         * ------------------------------------------------------------ */
        getActiveTab: function (tabList) {

            if (typeof tabList === 'string') {
                tabList = document.querySelector(tabList);
            }

            if (!tabList) return null;

            return tabList.querySelector(
                '[' + PSA.dataAttr('toggle') + '="tab"].' + PSA.cssClass('active')
            );
        },

        /* ------------------------------------------------------------
         * Get active pane inside a container (string selector or element)
         * ------------------------------------------------------------ */
        getActivePane: function (tabContent) {

            if (typeof tabContent === 'string') {
                tabContent = document.querySelector(tabContent);
            }

            if (!tabContent) return null;

            return tabContent.querySelector(
                PSA.selector('tab-pane') + '.' + PSA.cssClass('active')
            );
        }
    };

    /* ------------------------------------------------------------
     * Init Component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            Tabs.init();
        });
    } else {
        Tabs.init();
    }

    PSA.Tabs = Tabs;

})(window, document);