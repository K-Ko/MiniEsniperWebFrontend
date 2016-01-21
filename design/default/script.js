/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

/**
 * Toggle visibility of a DOM element identified by Id
 */
function toggle(id) {
    var e = document.getElementById(id);
    e.style.display = e.style.display == 'none' ? 'block' : 'none';
}

// ---------------------------------------------------------------------------

var logs = document.getElementsByClassName('log'), i = logs.length;

while (i--) {
    logs[i].scrollTop = logs[i].scrollHeight;
    if (collapseLogs && logs.length >= collapseLogs) {
        logs[i].parentElement.style.display = 'none';
    }
}

// Show page
document.body.style.opacity = 1;
