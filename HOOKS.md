# Available hooks

Some hooks have a **parameter by reference**.

Please refer to source code for context.

## index.php

    Line   28 : Hook::apply('init')
    Line   58 : Hook::apply('config.loaded', $config)
    Line   77 : Hook::apply('before.action', $page)
    Line   93 : Hook::apply('template.compile', $html)
    Line  114 : Hook::apply('template.compiled', $html)
    Line  121 : Hook::apply('template.compressed', $html)
    Line  128 : Hook::apply('before.render')
    Line  133 : Hook::apply('after.render')

## app/mef/Snipe.php

    Line   92 : Hook::apply('snipe.loaded', $this)
    Line  115 : Hook::apply('snipe.loaded', $this)

## design/default/index.html

    Line   36 : Hook::apply('head.style')
    Line   37 : Hook::apply('head.script')
    Line   43 : Hook::apply('before.content')
    Line   61 : Hook::apply('after.content')
    Line   66 : Hook::apply('body.script')
