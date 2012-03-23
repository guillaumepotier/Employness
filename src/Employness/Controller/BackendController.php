<?php

namespace Employness\Controller;

use Silex\Application;

/**
 * Admin Index
 */
$app->get('/admin', function() use($app)
{
    $days = $app['day.repository']->getDays();
    $days_keys = array_keys($days);

    return $app['twig']->render('admin.html.twig', array(
        'users'     =>  $app['user.repository']->findAll(),
        'days'      =>  array_reverse($days),
        'karmas'    =>  $app['karma.repository']->getKarmas($days[$days_keys[0]]['id'], $app['user.repository']),
    ));
})
->bind('admin');