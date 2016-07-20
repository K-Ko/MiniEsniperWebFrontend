# A minimal esniper web frontend

This repo will cover the handling of [esniper](http://esniper.sourceforge.net/),  a lightweight eBay sniping tool.

It works directly with the files created by esniper.

See the [manual page](http://esniper.sourceforge.net/esniper_man.html) for reference.

For auction definition see the [example auction file](http://esniper.sourceforge.net/sample_auction.txt).

Your configuration file (usually `~/.esniper`) must contain only

    username = <your ebay account name>
    password = <your password here>

If you have another esniper config file or want to protect the installation with basic auth, copy `config.default.php`

    # cp config.default.php config.php

and adjust the settings.

> Your installation directory must be writable for the web server running user, a unique data directory for file storage will be created on 1st run.

## Usage

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
