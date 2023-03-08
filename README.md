It is tested on ubuntu

How to install on Ubuntu, follow the next steps:

1) git clone https://github.com/SergM2014/import-app.git

2) cd import-app

3) ./vendor/bin/sail build --no-cache

4) ./vendor/bin/sail up -d

5) ./vendor/bin/sail shell

6) composer install

7) php artisan migrate --seed


the job of script is actualised via console command
to test the command print -> php artisan import:products


validations errors are outputed in console
settings for importing are kept in config/app.php in the import array



as for the interest you can visit http://localhost 
