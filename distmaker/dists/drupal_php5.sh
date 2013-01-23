#!/bin/sh
set -e

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

RSYNCOPTIONS="-avC --exclude=svn --exclude=.git --include=core"
RSYNCCOMMAND="$DM_RSYNC $RSYNCOPTIONS"
SRC=$DM_SOURCEDIR
TRG=$DM_TMPDIR/civicrm

# make sure and clean up before
if [ -d $TRG ] ; then
	rm -rf $TRG/*
fi

# copy all the stuff
for CODE in css i js l10n packages PEAR templates bin joomla CRM api drupal extern Reports install; do
  echo $CODE
  [ -d $SRC/$CODE ] && $RSYNCCOMMAND $SRC/$CODE $TRG
done

# delete any setup.sh or setup.php4.sh if present
if [ -d $TRG/bin ] ; then
  rm -f $TRG/bin/setup.sh
  rm -f $TRG/bin/setup.php4.sh
  rm -f $TRG/bin/setup.bat
fi


# copy selected sqls
if [ ! -d $TRG/sql ] ; then
	mkdir $TRG/sql
fi

for F in $SRC/sql/civicrm*.mysql $SRC/sql/counties.US.sql.gz $SRC/sql/case_sample*.mysql; do
	cp $F $TRG/sql
done

# copy docs
cp $SRC/agpl-3.0.txt $TRG
cp $SRC/gpl.txt $TRG 
cp $SRC/README.txt $TRG
cp $SRC/Sponsors.txt $TRG
cp $SRC/agpl-3.0.exception.txt $TRG
cp $SRC/drupal/civicrm.config.php.drupal $TRG/civicrm.config.php

# final touch
echo "<?php
function civicrmVersion( ) {
  return array( 'version'  => '$DM_VERSION',
                'cms'      => 'Drupal',
                'revision' => '$DM_REVISION' );
}
" > $TRG/civicrm-version.php

# gen tarball
cd $TRG/..
tar czf $DM_TARGETDIR/civicrm-$DM_VERSION-drupal.tar.gz --exclude l10n --exclude 'civicrm_*.??_??.mysql' civicrm
tar czf $DM_TARGETDIR/civicrm-$DM_VERSION-l10n.tar.gz --exclude '*.po' --exclude pot civicrm/l10n civicrm/sql/civicrm_*.??_??.mysql

# clean up
rm -rf $TRG
