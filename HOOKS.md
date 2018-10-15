# Available hooks

Some hooks have a **parameter by reference**.

Please refer to the source code to find out more about the context of the hook.

    +--------------------------------+------+-----------------------------------------------+
    | File                           | Line | Hook call                                     |
    +--------------------------------+------+-----------------------------------------------+
    | index.php                      |   27 | Hook::apply('init')                           |
    | index.php                      |   58 | Hook::apply('config.loaded', $config)         |
    | index.php                      |   78 | Hook::apply('before.action', $page)           |
    | index.php                      |   96 | Hook::apply('template.compile', $html)        |
    | index.php                      |  117 | Hook::apply('template.compiled', $html)       |
    | index.php                      |  124 | Hook::apply('template.compressed', $html)     |
    | index.php                      |  131 | Hook::apply('before.render')                  |
    | index.php                      |  136 | Hook::apply('after.render')                   |
    | app/mef/Snipe.php              |   98 | Hook::apply('snipe.loaded', $this)            |
    | app/mef/Snipe.php              |  121 | Hook::apply('snipe.loaded', $this)            |
    | design/default/index.html      |   36 | Hook::apply('head.style')                     |
    | design/default/index.html      |   43 | Hook::apply('before.content')                 |
    | design/default/index.html      |   62 | Hook::apply('after.content')                  |
    | design/default/index.html      |   69 | Hook::apply('body.script')                    |
    +--------------------------------+------+-----------------------------------------------+
