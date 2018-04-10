# Default design

To build your own design:

  - Copy all design files into a new directory

        # cp design/default design/new_design

  - Change `config.php` to use your `new_design`
  - If you think, your design is helpful
    - Change `README.md` and explain your changes
    - Send a pull request

# Template syntax

To inject PHP code use the following syntax.

## Simple `echo` commands

    {{$varname}}

will become:

```` php
<?php echo $varname ?>
````

## Other commands

Any other code surrounded by `{{` ... `}}`

    {{PHP code}}

will be used as is:

```` php
<?php PHP code ?>
````

## Translations

C surrounded by `{|` ... `|}`

    {|translation_key|}

will be used from `language/<language>.php`
