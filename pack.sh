#!/bin/sh
# $Id: pack.sh,v 1.1.1.1 2008-09-29 14:41:17 oops Exp $

cp -af package.xml.tmpl package.xml
list=$(grep "md5sum" ./package.xml | sed 's/.*"@\|@".*//g')

for i in $list
do
	md5s=$(md5sum $i | awk '{print $1}')
	perl -pi -e "s!\@${i}\@!${md5s}!g" ./package.xml
done

curdate=$(date +'%Y-%m-%d')
curtime=$(date +'%H:%M:%S')

perl -pi -e "s!\@curdate\@!${curdate}!g" ./package.xml
perl -pi -e "s!\@curtime\@!${curtime}!g" ./package.xml

[ -z "$1" ] && pear package
