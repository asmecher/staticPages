echo "Installing OJS"

startdir=$(pwd)

cd ~

# Set up the OJS database
echo "CREATE DATABASE ojs DEFAULT CHARSET utf8" | mysql -root
echo "CREATE USER 'ojs'@'localhost' IDENTIFIED BY 'ojs'" | mysql -uroot
echo "GRANT ALL ON ojs.* TO 'ojs'@'localhost'" | mysql -uroot
echo "FLUSH PRIVILEGES" | mysql -uroot

# cd /var/www

# Clone the OJS repository
git clone https://github.com/pkp/ojs 
cd ojs
git submodule update --init --recursive
# ./tools/startSubmodulesTRAVIS.sh
cp config.TEMPLATE.inc.php config.inc.php
mkdir files
# chgrp -R www-data cache public ~/files config.inc.php
# chmod -R ug+w cache public ~/files config.inc.php

# Install Composer dependencies
curl -sS https://getcomposer.org/installer | php
cd lib/pkp
php ../../composer.phar -v update
cd ../..
cd plugins/paymethod/paypal
php ../../../composer.phar -v update
cd ../../..
cd plugins/generic/citationStyleLanguage
php ../../../composer.phar -v update
cd ../../..

# Build the vue.js dependencies
npm install
npm run build

cd $startdir
./prepare-webserver.sh

# Install OJS
wget --tries=1 -O - --post-data="adminUsername=admin&adminPassword=admin&adminPassword2=admin&adminEmail=ojs@mailinator.com&locale=en_US&additionalLocales[]=en_US&clientCharset=utf-8&connectionCharset=utf8&databaseCharset=utf8&filesDir=$(pwd | sed -e 's/\//%2f/g')%2ffiles&encryption=sha1&databaseDriver=mysqli&databaseHost=localhost&databaseUsername=ojs&databasePassword=ojs&databaseName=ojs&oaiRepositoryId=ojs2.localhost" "http://localhost/ojs/index.php/index/install/install"

