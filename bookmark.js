javascript: (function() {
    var w = window,
        d = document,
        s =
            d.getElementById('descItemNumber').innerText ||
            w.getSelection().toString();
    if (s) {
        var e = encodeURIComponent,
            p = w.open(
                'http://es.mydip.net?api=bookmark&token=KnutKohl&name=' +
                    e(d.title) +
                    '&item=' +
                    e(s),
                'es_popup',
                'left=' +
                    ((w.screenX || w.screenLeft) + 10) +
                    ',top=' +
                    ((w.screenY || w.screenTop) + 10) +
                    ',height=150px,width=400px,resizable=1,alwaysRaised=1'
            );
        w.setTimeout(function() {
            p.focus();
        }, 300);
    } else {
        w.alert('Please select item Id before!');
    }
})();
