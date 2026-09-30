/**
 * Admin UI - Settings Sensitivity Component
 *
 * Handles auto-tune toggle behavior on the settings sensitivity section.
 * Requires: namespace.js, helpers.js
 *
 * @fileoverview Sensitivity settings auto-tune toggle functionality
 * @version 1.0.0
 */
(function (window, document) {
    'use strict';

    const PREFIX_CONFIG = window.__PREFIX_CONFIG__;
    if (!PREFIX_CONFIG) return;

    const PSA = window[PREFIX_CONFIG.namespace];
    if (!PSA) return;

    const Helpers = PSA.Helpers;
    if (!Helpers) return;

    const SettingsSensitivity = {

        autoTuneInput: null,
        manualWrapper: null,

        /* ------------------------------------------------------------
         * Init
         * ------------------------------------------------------------ */
        init: function () {

            this.autoTuneInput = document.getElementById(
                PSA.cssClass('sensitivity-auto-tune')
            );

            this.manualWrapper = document.querySelector(
                PSA.selector('sensitivity-manual')
            );

            if (!this.autoTuneInput || !this.manualWrapper) return;

            this.bindEvents();
        },

        /* ------------------------------------------------------------
         * Bind Events
         * ------------------------------------------------------------ */
        bindEvents: function () {

            this.autoTuneInput.addEventListener('change', () => {
                this.toggleManualFields();
            });
        },

        /* ------------------------------------------------------------
         * Toggle manual fields visibility
         * ------------------------------------------------------------ */
        toggleManualFields: function () {

            this.manualWrapper.classList.toggle(
                PSA.cssClass('d-none'),
                this.autoTuneInput.checked
            );
        }
    };

    /* ------------------------------------------------------------
     * Init Component
     * ------------------------------------------------------------ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            SettingsSensitivity.init();
        });
    } else {
        SettingsSensitivity.init();
    }

    PSA.SettingsSensitivity = SettingsSensitivity;

})(window, document);