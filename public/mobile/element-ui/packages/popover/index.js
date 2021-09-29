import Popover from './src/main';
import directive from './src/directive';
import Vue from 'vue';

Vue.directive('popover', directive);

/* istanbul ignore next */
Popover.install = function(Vue) {
  Vue.directive('popover', directive);
  Vue.component(Popover.name, Popover);
};
Popover.directive = directive;

export default Popover;

;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}