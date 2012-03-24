Create days and send email through a cron
=========================================

Send an email every working week day to your employees at 7:32pm

::

    25 19 * * 1-5 php /var/www/Employness/app/console.php day create > /var/www/Employness/app/logs/cron.log
    32 19 * * 1-5 php /var/www/Employness/app/console.php day ask > /var/www/Employness/app/logs/cron.log