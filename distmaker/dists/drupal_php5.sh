#!/bin/bash

# This script assumes
# that DAOs are generated
# and all the necessary conversions had place!

P=`dirname $0`
CFFILE=$P/../distmaker.conf

if [ ! -f $CFFILE ] ; then
	echo "NO DISTMAKER.CONF FILE!"
	exit 1
else
	for l in `cat $CFFILE`; do export $l; done
fi

RSYNCOPTIONS="-avC --exclude=svn"
RSYNCCOMMAND="rsync $RSYNCOPTIONS"
SRC=$DM_SOURCEDIR
TRG=$DM_TMPDIR/civicrm

# make sure and clean up before
if [ -d $TRG ] ; then
	rm -rf $TRG/*
fi

# copy all the stuff
for CODE in css i js l10n packages PEAR templates bin mambo CRM api modules extern; do
  echo $CODE
  [ -d $SRC/$CODE ] && $RSYNCCOMMAND $SRC/$CODE $TRG
done

# delete any setup.sh or setup.php4.sh if present
if [ -d $TRG/bin ] ; then
  rm -f $TRG/bin/setup.sh
  rm -f $TRG/bin/setup.php4.sh
  rm -f $TRG/bin/setup.bat
fi

# delete current config.inc.php
rm -f $TRG/modules/config.inc.php $TRG/mambo/config.inc.php

# copy selected sqls
if [ ! -d $TRG/sql ] ; then
	mkdir $TRG/sql
fi

for F in $SRC/sql/civicrm_*.mysql; do 
	cp $F $TRG/sql
done


# copy docs
cp $SRC/license.txt $TRG
cp $SRC/affero_gpl.txt $TRG
cp $SRC/gpl.txt $TRG 
cp $SRC/Readme.txt $TRG

# final touch
REV=`svnversion -n $SRC`
echo "1.3.$REV Drupal PHP5" > $TRG/civicrm-version.txt


# gen tarball
cd $TRG/..
tar czf $DM_TARGETDIR/civicrm-drupal-php5-SNAPSHOT-rev$REV.tgz civicrm

# clean up
rm -rf $TRG
