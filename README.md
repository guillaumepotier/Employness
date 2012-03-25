# General Info

This project allows you to see what is your daily employees' satisfaction at work.
This is a pet project that aims to know more Silex and it interactions with Sf2 Components.

# Installation

## Getting the project and its submodules

```
cp app/config/config.php.dist app/config/config.php
git submodule update --init
chmod -R 777 app/logs
vi app/config/config.php
```

## Loading database

```
mysql -u root -p create employness_dev
mysql -u root -p employness_dev < misc/schema.sql
```

## commands

Two commands are currently available. You can see all the commands doing `php app/console.php`

### users

````
php app/console.php user:create -e email -p password
php app/console.php user:create -e email -p password -a admin
````

### days

Create a new day (the today day) in the db:

````
php app/console.php day create
````

Ask for ratings for today:

````
php app/console.php day ask
````

### crontab for days commands

See misc/crontab.rst for more info on how to send daily emails through cron

# TODOS

* Add french messages
* Add categories/tags for employees.
* Add comment option when you rate your day
* Improve admin backend (mainly usermanagement)
* Possibility to create multi companies

# Screens

![Employness homepage](https://raw.github.com/guillaumepotier/Employness/master/misc/screen_1.png)
<p>Employness homepage</p>

![Employness backend](https://raw.github.com/guillaumepotier/Employness/master/misc/screen_2.png)
<p>Employness backend</p>