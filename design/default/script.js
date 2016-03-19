/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

/**
 * Toggle visibility of DOM element by id
 */
function toggle(selector, force) {
    if (selector.substr(0,1) == '#') {
        var e = document.getElementById(selector.substr(1));
        if (arguments.length == 2) {
            e.style.display = !!force ? 'block' : 'none';
        } else {
            e.style.display = e.style.display == 'none' ? 'block' : 'none';
        }
    } else if (selector.substr(0,1) == '.') {
        var e = document.getElementsByClassName(selector.substr(1)), i = e.length;
        // if before while
        if (arguments.length == 2) {
            while (i--) e[i].style.display = !!force ? 'block' : 'none';
        } else {
            while (i--) e[i].style.display = e[i].style.display == 'none' ? 'block' : 'none';
        }
    }
}

/**
 * Hide all logs, show the one if provided
 */
function toggleLogs(id) {
    toggle('.log', false);
    if (arguments.length) {
        toggle(id, true);
        var e = document.getElementById(id.substr(1));
        e.scrollTop = e.scrollHeight;
    }
}

// ---------------------------------------------------------------------------
// Hide logs only if there are more than one 
if (document.getElementsByClassName('log').length > 1) toggleLogs();

// Finally show the page
document.body.style.opacity = 1;
