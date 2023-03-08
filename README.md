Application is created for testing product import from xlxs file to DB

Instalation is tested localy on ubuntu 22.04

Please, follow the next steps:

1) git clone https://github.com/SergM2014/import-app.git

2) cd import-app

3) ./vendor/bin/sail build --no-cache

4) ./vendor/bin/sail up -d

5) ./vendor/bin/sail shell

6) composer install

7) php artisan migrate --seed


the job of script is actualised via console command
to test the command print ->

 php artisan import:products



validations errors are outputed in console
settings for importing are kept in config/app.php in the import array

the products which were failed to be inserted in DB are counted and logged



as for the interest you can visit http://localhost 

application is able to import huge xlsx file, hence it uses generator.
thanks to https://github.com/shuchkin/simplexlsx package