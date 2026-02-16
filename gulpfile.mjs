/**
 * Gulp Build Configuration
 *
 * Canonical prefix system
 * Single source of truth: config/prefix.config.cjs
 */

import gulp from 'gulp';
import cleanCSS from 'gulp-clean-css';
import terser from 'gulp-terser';
import rename from 'gulp-rename';
import concat from 'gulp-concat';
import {deleteAsync} from 'del';
import gulpSass from 'gulp-sass';
import * as dartSass from 'sass';
import fs from 'fs';
import path from 'path';
import {fileURLToPath} from 'url';
import {createRequire} from 'module';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const sass = gulpSass(dartSass);
const require = createRequire(import.meta.url);

/**
 * Load Prefix Config
 */
function loadPrefixConfig() {
    try {
        const configPath = path.resolve(__dirname, 'config/prefix.config.cjs');
        delete require.cache[configPath];
        return require(configPath);
    } catch (error) {
        console.error('Error loading prefix config:', error);

        return {
            base: 'proactive-site-advisor',
            basePhp: 'proactive_site_advisor',
            baseConst: 'PROACTIVE_SITE_ADVISOR',
            namespace: 'ProactiveSiteAdvisor',
            configObject: 'proactiveSiteAdvisorConfig',
        };
    }
}

/**
 * Paths
 */
const paths = {
    config: {
        src: 'config/prefix.config.cjs',
        scssOut: 'assets-src/scss/_prefix.scss',
        jsOut: 'assets-src/scripts/core/namespace.js',
        phpOut: 'src/Config/PrefixConfig.php',
    },
    scss: {
        src: 'assets-src/scss/admin.scss',
        watch: 'assets-src/scss/**/*.scss',
        dest: 'assets/css/',
    },
    js: {
        src: [
            'assets-src/scripts/core/namespace.js',
            'assets-src/scripts/core/config.js',
            'assets-src/scripts/core/helpers.js',
            'assets-src/scripts/components/theme-switcher.js',
            'assets-src/scripts/components/alert-card.js',
            'assets-src/scripts/components/promo-banner.js',
            'assets-src/scripts/components/admin-notices.js',
            'assets-src/scripts/admin.js',
        ],
        watch: 'assets-src/scripts/**/*.js',
        dest: 'assets/js/',
    },
};

/**
 * Generate Prefix Files
 */
export async function generatePrefixFiles(cb) {
    const p = await loadPrefixConfig();

    const base = p.base;
    const basePhp = p.basePhp;
    const baseConst = p.baseConst;
    const namespace = p.namespace;
    const configObject = p.configObject;

    console.log(`\nGenerating prefix files from base: "${base}"\n`);

    // ===============================
    // SCSS
    // ===============================
    const scssContent = `// AUTO-GENERATED FILE - DO NOT EDIT

$base: '${base}' !default;

@function css-class($name) {
    @return $base + '-' + $name;
}

@function css-var($name) {
    @return var(--#{$base}-#{$name});
}

@function css-var-name($name) {
  @return --#{$base}-#{$name};
}

@function data-attr($name, $value: null) {
    @if $value {
        @return '[data-#{$base}-#{$name}="#{$value}"]';
    }
    @return '[data-#{$base}-#{$name}]';
}
`;

    fs.writeFileSync(paths.config.scssOut, scssContent);

    // ===============================
    // JS Namespace (Fully Canonical)
    // ===============================
    const jsContent = `(function(window){
'use strict';

var CONFIG = Object.freeze({
    base: '${base}',
    basePhp: '${basePhp}',
    namespace: '${namespace}',
    configObject: '${configObject}'
});

window.__PREFIX_CONFIG__ = CONFIG;

var NS = CONFIG.namespace;

window[NS] = window[NS] || {};
window[NS].__config = CONFIG;

window[NS].cssClass = function(name){
    return CONFIG.base + '-' + name;
};

window[NS].cssVar = function(name){
    return '--' + CONFIG.base + '-' + name;
};

window[NS].dataAttr = function(name){
    return 'data-' + CONFIG.base + '-' + name;
};

window[NS].dataSelector = function(name,value){
    var attr = 'data-' + CONFIG.base + '-' + name;
    return value !== undefined
        ? '[' + attr + '="' + value + '"]'
        : '[' + attr + ']';
};

window[NS].event = function(name){
    return CONFIG.base + ':' + name;
};

window[NS].dispatch = function(name,detail,target){
    var event = new CustomEvent(
        CONFIG.base + ':' + name,
        {
            detail: detail || {},
            bubbles: true,
            cancelable: true
        }
    );
    (target || document).dispatchEvent(event);
};

window[NS].storageKey = function(key){
    return CONFIG.base + '-' + key;
};

window[NS].selector = function(name){
    return '.' + CONFIG.base + '-' + name;
};

window[NS].ajaxAction = function(name){
    return CONFIG.basePhp + '_' + name;
};

})(window);
`;

    fs.writeFileSync(paths.config.jsOut, jsContent);

    // ===============================
    // PHP (Canonical & DRY)
    // ===============================
    const phpContent = `<?php

namespace ${namespace}\\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PrefixConfig
 *
 * Provides helper methods to generate prefixed identifiers
 * used across the plugin.
 *
 * @package ${namespace}\\Config
 * @version 1.0.0
 */
final class PrefixConfig
{
    /**
     * Base slug used for CSS classes and handles (kebab-case).
     */
    public const BASE = '${base}';

    /**
     * PHP-safe prefix (snake_case).
     */
    public const PREFIX = '${basePhp}';

    /**
     * Uppercase constant prefix.
     */
    public const CONSTANT_PREFIX = '${baseConst}';

    /**
     * Global JS config object name.
     */
    public const CONFIG_OBJECT = '${configObject}';

    /**
     * Prevent instantiation.
     */
    private function __construct(){}

    /**
     * Generate a prefixed CSS class.
     *
     * @param string $name Class suffix.
     * @return string
     */
    public static function css(string $name): string
    {
        return self::BASE . '-' . $name;
    }

    /**
     * Generate a prefixed data attribute name.
     *
     * @param string $name Attribute suffix.
     * @return string
     */
    public static function dataAttr(string $name): string
    {
        return 'data-' . self::BASE . '-' . $name;
    }

    /**
     * Generate a script/style handle.
     *
     * @param string $name Handle suffix.
     * @return string
     */
    public static function handle(string $name): string
    {
        return self::BASE . '-' . $name;
    }

    /**
     * Generate an Ajax action name.
     *
     * @param string $name Action suffix.
     * @return string
     */
    public static function ajaxAction(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }

    /**
     * Generate a nonce action name.
     *
     * @param string $name Nonce suffix.
     * @return string
     */
    public static function nonce(string $name = 'nonce'): string
    {
        return self::PREFIX . '_' . $name;
    }

    /**
     * Generate a database table name (without $wpdb prefix).
     *
     * @param string $name Table suffix.
     * @return string
     */
    public static function table(string $name): string
    {
        return self::PREFIX . '_' . $name;
    }
}
`;

    const phpDir = path.dirname(paths.config.phpOut);
    if (!fs.existsSync(phpDir)) {
        fs.mkdirSync(phpDir, {recursive: true});
    }

    fs.writeFileSync(paths.config.phpOut, phpContent);

    console.log('\nPrefix files generated successfully!\n');
    cb();
}

/**
 * Clean
 */
export async function clean() {
    await deleteAsync([paths.scss.dest, paths.js.dest]);
}

/**
 * SCSS
 */
export function compileScss() {
    return gulp
            .src(paths.scss.src, {allowEmpty: true})
            .pipe(sass().on('error', sass.logError))
            .pipe(cleanCSS({compatibility: 'ie11'}))
            .pipe(rename({suffix: '.min'}))
            .pipe(gulp.dest(paths.scss.dest));
}

/**
 * JS
 */
export function compileJs() {
    return gulp
            .src(paths.js.src, {allowEmpty: true})
            .pipe(concat('admin.js'))
            .pipe(terser())
            .pipe(rename({suffix: '.min'}))
            .pipe(gulp.dest(paths.js.dest));
}

export const build = gulp.series(
        generatePrefixFiles,
        clean,
        gulp.parallel(compileScss, compileJs)
);

export default build;
