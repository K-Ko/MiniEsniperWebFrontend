# A minimal esniper web frontend

![](https://img.shields.io/github/release/K-Ko/MiniEsniperWebFrontend.svg)

This repo will cover the handling of [esniper](http://esniper.sourceforge.net/),  a lightweight eBay sniping tool.

It works directly with the files created by esniper.

See the [manual page](http://esniper.sourceforge.net/esniper_man.html) for reference.

For auction definition see the [example auction file](http://esniper.sourceforge.net/sample_auction.txt).

## Configuration

If you need to adjust configuration settings, don't change `config.default.php`, make a local copy

    # cp config.default.php config.local.php

and adjust the settings for your needs.

> If you don't change the location for the data directory, your installation directory must be **writable
> for the web server running user**. A unique data directory for file storage will than be created here on 1st run.
>
> Directory `design_cpl` must also be writable to store the compiled templates.

## Usage

### Login

"Login" with your ebay username and password.
This will not checked, only used for esniper starts.
Credentials will be stored in session only.

### Add auctions

Give your auction group a name and key in all auctions and at least one bid for all auctions in 1st line.

Separate auction Id and price with at least one space.

Start the sniping.

### Stop sniping

Click on the edit button near the auction group name.

This will stop the running esniper process for this group and you can adjust the auction snipes.

### Remove auction groups

  - 1st stop, if active
  - Click on the trash button near the auction group name.

This will remove the auction group and the log file.

## Hooks

When needed, the system can be extended by hooks, see `index.php` for available hooks

    Hook::apply(...);

and `hooks.dist.php` for examples.

## Translations

* Checkout a new branch
* Copy `language/en.php` to your language shortcut
* Translate the items
* Add your language to `config.default.php` (with locales if needed)
* Make a pull request :-)
