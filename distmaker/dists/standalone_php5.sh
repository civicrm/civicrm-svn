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

# copy all the stuff
for CODE in css i js l10n packages PEAR templates bin CRM api extern Reports standalone install; do
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

for F in $SRC/sql/civicrm*.mysql; do 
	cp $F $TRG/sql
done

# remove Quest
find $TRG -depth -name 'Quest' -exec rm -r {} \;

# delete SimpleTest
if [ -d $TRG/packages/SimpleTest ] ; then
  rm -rf $TRG/packages/SimpleTest
fi
if [ -d $TRG/packages/drupal ] ; then
  rm -rf $TRG/packages/drupal
fi

# delete UFPDF's stuff not required on installations
if [ -d $TRG/packages/ufpdf/ttf2ufm-src ] ; then
  rm -rf $TRG/packages/ufpdf/ttf2ufm-src
fi

# copy docs
cp $SRC/agpl-3.0.txt $TRG
cp $SRC/gpl.txt $TRG 
cp $SRC/README.txt $TRG
cp $SRC/standalone/civicrm.config.php.standalone $TRG/civicrm.config.php

# final touch
echo "$DM_VERSION Standalone PHP5 $DM_REVISION" > $TRG/civicrm-version.txt


# gen tarball
cd $TRG/..
tar czf $DM_TARGETDIR/civicrm-$DM_VERSION-standalone.tar.gz --exclude l10n --exclude 'civicrm_*.??_??.mysql' civicrm

# clean up
rm -rf $TRG
