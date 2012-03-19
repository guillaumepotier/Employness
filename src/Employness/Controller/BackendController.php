<?php

use Silex\Application;

/**
 * Admin Index
 */
$app->get('/admin', function() use($app)
{
    $user = $days = array();
    $users_query = $app['db']->query("SELECT * FROM employness_users ORDER BY id ASC");
    $days_query = $app['db']->query("SELECT * FROM employness_days ORDER BY id DESC LIMIT 30");

    while ($row = $users_query->fetch()) {
        $users[] = $row;
    }

    while ($row = $days_query->fetch()) {
        // TODO: create layer that automatically unserialise arrays when fetch db data..
        $days[] = array(
            'id'            =>  $row['id'],
            'day'           =>  $row['day'],
            'karma'         =>  $row['karma'],
            'participants'  =>  unserialize($row['participants']),
        );
    }

    return $app['twig']->render('admin.html.twig', array(
        'users' =>  $users,
        'days'  =>  $days,
    ));
})
->bind('admin');