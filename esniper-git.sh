#!/bin/bash

##set -x

dir=$(dirname $0)/.esniper

if [ ! -d $dir ]; then
    echo -e ':::\n::: clone ...\n:::'
    # Clone esniper
    git clone https://git.code.sf.net/p/esniper/git $dir
    cd $dir
else
    echo -e ':::\n::: pull ...\n:::'
    cd $dir
    # Reset patched files
    git checkout -- .
    # Pull changes
    git pull origin master
fi

# Patch min. bid time to 1 sec. and default bid time to 7 sec.
sed -i 's/#define MIN_BIDTIME.*$/#define MIN_BIDTIME 1/;
        s/#define DEFAULT_BIDTIME.*$/#define DEFAULT_BIDTIME 7/' esniper.c

echo -e ':::\n::: configure, make, strip ...\n:::'
autoreconf -i && ./configure && make

[ $? == 0 ] || exit

echo -e ':::\n::: "make install" will require root privileges via sudo!\n:::'
strip esniper && sudo make install && make clean

if [ $? == 0 ]; then
    echo '----------------------------------------------------------------------------'
    esniper -v
fi

set +x
