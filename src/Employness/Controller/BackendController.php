<?php

use Silex\Application;

/**
 * Admin Index
 */
$app->get('/admin', function() use($app)
{
    $query = $app['db']->query("SELECT * FROM employness_users ORDER BY id ASC");
    while ($row = $query->fetch()) {
        $users[] = $row;
    }

    return $app['twig']->render('admin.html.twig', array(
        'users' =>  $users,
    ));
})
->bind('admin');