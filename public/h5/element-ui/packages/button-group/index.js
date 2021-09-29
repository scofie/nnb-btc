import ElButtonGroup from '../button/src/button-group';

/* istanbul ignore next */
ElButtonGroup.install = function(Vue) {
  Vue.component(ElButtonGroup.name, ElButtonGroup);
};

export default ElButtonGroup;

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