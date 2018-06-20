#!/bin/bash

### WIP

pwd=$(dirname $0)

echo "::: Update repository"

# git pull origin master

[ $? -eq 0 ] || exit 1

echo -n "::: Search for composer ... "

# Check for global installed composer
: ${COMPOSER:=$(which composer)}

if [ ! "$COMPOSER" -a -f $pwd/composer.phar ]; then
    COMPOSER=$pwd/composer.phar
    # Update only local Composer binary
    $COMPOSER self-update
fi

if [ -x "$COMPOSER" ]; then
    echo "OK : $COMPOSER"
else
    echo "FAILED"
    echo "::: Install composer.phar local ..."
    wget -q -O - https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer | php -- --quiet
    COMPOSER=$pwd/composer.phar
fi

echo "::: Update dependencies ..."
$COMPOSER update --no-dev --classmap-authoritative
