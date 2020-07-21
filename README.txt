

#Create a database in your database server
CREATE DATABASE `thehub` /*!40100 COLLATE 'utf8mb4_unicode_ci' */;

#create storage and container (blob) in Azure
https://portal.azure.com

#Setting Permissions for different folders
sudo chmod -R 775 storage/ bootstrap/cache/ vendor/

#Installing Composer dependency
composer install

#Setting up .env
.env.example .env
chmod 777 .env


#Edit .env file
APP_ENV=  #local, staging, or production
APP_DEBUG= #true or false 
APP_URL=https://admin.example.com #admin url
USER_URL=https://my.example.com #your users portal url (the hub)

DB_CONNECTION=mysql #database type
DB_HOST= #database server host name or ip
DB_PORT= #database server port
DB_DATABASE=thehub #database name (thehub)
DB_USERNAME= #database name
DB_PASSWORD= #database password

AZURE_STORAGE_NAME= #storage name (thehubstorage)
AZURE_STORAGE_KEY=  #storage key (example: cb+gorRUWgARXtyD0nw/gorRUWgARXtyD0nw==)
AZURE_STORAGE_CONTAINER= #container name (upload)

#----[From https://docs.microsoft.com/en-us/azure/mysql/howto-configure-ssl]----
# 1- Obtain SSL certificate from https://www.digicert.com/CACerts/BaltimoreCyberTrustRoot.crt.pem
# 2- copy BaltimoreCyberTrustRoot.crt.pem to database/ folder
# 3- Write the full (**server**) path in MYSQL_ATTR_SSL_CA
MYSQL_ATTR_SSL_CA=/home/site/wwwroot/database/BaltimoreCyberTrustRoot.crt.pem

#Generate app key
php key:generate

#Create database tables
php artisan migrate:fresh 

#Create default data
php artisan db:seed

#Setup admin email and password 
php artisan theHubadmin:install-admin

#Running NPM install
npm install

#Run NPM packaging 
npm run prod


