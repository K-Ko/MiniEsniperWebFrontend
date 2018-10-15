/**
 * Based on
 * http://www.sitepoint.com/introduction-to-page-visibility-api/
 */
var pageVisibilityApi = (function() {
    /**
     * Private variables & methods
     */
    var options = {
            onShow: null,
            onHide: null,
            delay: 0, // Seconds
            debug: false
        },
        browserPrefix = null,
        timeoutId = false;

    /**
     *
     */
    function log(msg) {
        options.debug && console.log('Page visibility API: ' + msg);
    }

    /**
     *
     */
    function isVisible() {
        return document.hidden === false || document[browserPrefix + 'Hidden'] === false;
    }

    /**
     * State change listener
     */
    function onStateChange() {
        if (isVisible()) {
            log('Page become visible');
            if (options.onShow) {
                timeoutId = setTimeout(options.onShow, options.delay * 1000);
            }
        } else {
            log('Page become hidden');
            if (timeoutId) {
                log('Clear timeout');
                clearTimeout(timeoutId);
            }
            if (options.onHide) {
                options.onHide();
            }
        }
    }

    /**
     * Public interface
     */
    return {
        /**
         *
         */
        Init: function(opts) {
            $.extend(options, opts);

            if (document.hidden !== undefined) {
                browserPrefix = '';
            } else {
                var browserPrefixes = ['webkit', 'moz', 'ms', 'o'];
                // Test all vendor prefixes
                for (var i = 0; i < browserPrefixes.length; i++) {
                    if (document[browserPrefixes[i] + 'Hidden'] != undefined) {
                        browserPrefix = browserPrefixes[i];
                        break;
                    }
                }
            }

            if (browserPrefix !== null) {
                let evListener = browserPrefix + 'visibilitychange';
                log('Add addEventListener "' + evListener + '"');
                document.addEventListener(evListener, onStateChange);
            } else {
                log('Not available');
            }
        },

        /**
         *
         * @return boolean
         */
        isVisible: function() {
            return isVisible();
        },

        /**
         *
         * @param {integer} delay Seconds
         */
        setDelay: function(delay) {
            options.delay = +delay;
        },

        /**
         *
         * @param {boolean} debug
         */
        setDebug: function(debug) {
            options.debug = !!debug;
        },

        /**
         * https://stackoverflow.com/a/7356528
         *
         * @param {callable} callback
         */
        setOnShow: function(callback) {
            options.onShow = callback || null;
        },

        /**
         *
         * @param {callable} callback
         */
        setOnHide: function(callback) {
            options.onHide = callback || null;
        }
    };
})();
