/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 * @version    1.0.0
 */

function toggle(id) {
    var e = document.getElementById(id);
    if (e.style.display == 'block') {
        e.style.display = 'none';
    } else {
        e.style.display = 'block';
    }
}

var logs = document.getElementsByClassName('log'), i = logs.length;

while (i--) {
    logs[i].scrollTop = logs[i].scrollHeight;
    logs[i].parentElement.style.display = 'none';
}

document.body.style.opacity = 1;
