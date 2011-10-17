#!/bin/sh

# This script assumes
# that DAOs are generated
# and all the necessary conversions had place!

P=`dirname $0`
CFFILE=$P/../distmaker.conf

if [ ! -f $CFFILE ] ; then
	echo "NO DISTMAKER.CONF FILE!"
	exit 1
else
	. $CFFILE	
fi

RSYNCOPTIONS="-avC --exclude=svn"
RSYNCCOMMAND="$DM_RSYNC $RSYNCOPTIONS"
SRC=$DM_SOURCEDIR
TRG=$DM_TMPDIR/civicrm

# make sure and clean up before
if [ -d $TRG ] ; then
	rm -rf $TRG/*
fi

if [ ! -d $TRG ] ; then
	mkdir $TRG
fi

if [ ! -d $TRG/civicrm ] ; then
	mkdir $TRG/civicrm
fi

# copy all the stuff
for CODE in css i js l10n packages PEAR templates bin joomla CRM api drupal extern Reports install; do
  echo $CODE
  [ -d $SRC/$CODE ] && $RSYNCCOMMAND $SRC/$CODE $TRG/civicrm
done

# delete any setup.sh or setup.php4.sh if present
if [ -d $TRG/civicrm/bin ] ; then
  rm -f $TRG/civicrm/bin/setup.sh
  rm -f $TRG/civicrm/bin/setup.php4.sh
  rm -f $TRG/civicrm/bin/setup.bat
fi

# copy selected sqls
if [ ! -d $TRG/civicrm/sql ] ; then
	mkdir $TRG/civicrm/sql
fi

for F in $SRC/sql/civicrm*.mysql $SRC/sql/counties.US.sql.gz; do 
	cp $F $TRG/civicrm/sql
done

for F in $SRC/WordPress/*; do 
	cp $F $TRG
done

# remove Quest
find $TRG/civicrm -depth -name 'Quest' -exec rm -r {} \;

# delete SimpleTest
if [ -d $TRG/civicrm/packages/SimpleTest ] ; then
  rm -rf $TRG/civicrm/packages/SimpleTest
fi
if [ -d $TRG/civicrm/packages/drupal ] ; then
  rm -rf $TRG/civicrm/packages/drupal
fi

# delete UFPDF's stuff not required on installations
if [ -d $TRG/civicrm/packages/ufpdf/ttf2ufm-src ] ; then
  rm -rf $TRG/civicrm/packages/ufpdf/ttf2ufm-src
fi

# copy docs
cp $SRC/agpl-3.0.txt $TRG/civicrm
cp $SRC/gpl.txt $TRG/civicrm
cp $SRC/README.txt $TRG/civicrm

# final touch
echo "<?php
function civicrmVersion( ) {
  return array( 'version'  => '$DM_VERSION',
                'cms'      => 'Wordpress',
                'revision' => '$DM_REVISION' );
}
" > $TRG/civicrm/civicrm-version.php

# gen tarball
cd $TRG
$DM_ZIP -r -9 $DM_TARGETDIR/civicrm-$DM_VERSION-wordpress.zip * -x '*/l10n/*' -x '*/sql/civicrm_*.??_??.mysql'
# clean up
rm -rf $TRG
