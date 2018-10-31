# Available hooks

Some hooks have a **parameter by reference**.

Please refer to the source code to find out more about the context of the hook.

    +--------------------------------+------+-----------------------------------------------+
    | File                           | Line | Hook call                                     |
    +--------------------------------+------+-----------------------------------------------+
    | index.php                      |   31 | Hook::apply('init')                           |
    | index.php                      |   70 | Hook::apply('config.loaded', $config)         |
    | index.php                      |   92 | Hook::apply('before.action', $page)           |
    | index.php                      |  110 | Hook::apply('template.compile', $html)        |
    | index.php                      |  131 | Hook::apply('template.compiled', $html)       |
    | index.php                      |  140 | Hook::apply('template.compressed', $html)     |
    | index.php                      |  147 | Hook::apply('before.render')                  |
    | index.php                      |  152 | Hook::apply('after.render')                   |
    | app/Snipe.php                  |   98 | Hook::apply('snipe.loaded', $this)            |
    | app/Snipe.php                  |  121 | Hook::apply('snipe.loaded', $this)            |
    | design/default/index.phtml     |   36 | Hook::apply('head.style')                     |
    | design/default/index.phtml     |   43 | Hook::apply('before.content')                 |
    | design/default/index.phtml     |   62 | Hook::apply('after.content')                  |
    | design/default/index.phtml     |   68 | Hook::apply('body.script')                    |
    +--------------------------------+------+-----------------------------------------------+
