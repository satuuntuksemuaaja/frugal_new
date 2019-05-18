# Frugal Kitchen v3
###### 3rd iteration of the Frugal Kitchen internal management application

## How to get it up and running
```
git clone git@github.com:vocalogic/fk3.git
composer install

cp .env.example .env
# edit env variables as needed (mysql database, user, pass for example)
# create database name, user, grant as you like

./artisan key:generate

# You need a copy of the old (current) database in order to run seeds
# because it uses previous data to "re-seed" based on new schema changes.
# mysql vocalcrm < ../oldfk.sql

./artisan db:migrate

# For the first time, uncomment this line in database/seeds/DatabaseSeeder.php
#  // $this->call(UserSeeder::class);
# After successful seeding, re-comment.  Todo - figure out idempotency issue here.
./artisan db:seed
./artisan serve

# For the first time, you might need to go to /logout, and log back in with the default user:
# u: chorne@core3networks.com
# p: frugal
#
# If you don't have an admin panel, you might need to go to `/admin/users` and update the
# chorne@core3networks.com `Group` to 'Admin'.
```
