(function(window){
'use strict';

const CONFIG = Object.freeze({
    base: 'psa',
    basePhp: 'proactive_site_advisor',
    namespace: 'PSA',
    configObject: 'psaConfig'
});

window.__PREFIX_CONFIG__ = CONFIG;

const NS = CONFIG.namespace;

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
    const attr = 'data-' + CONFIG.base + '-' + name;
    return value !== undefined
        ? '[' + attr + '="' + value + '"]'
        : '[' + attr + ']';
};

window[NS].event = function(name){
    return CONFIG.base + ':' + name;
};

window[NS].dispatch = function(name,detail,target){
    const event = new CustomEvent(
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
