<?php

namespace Employness\Controller;

use Silex\Application;

/**
 * Admin Index
 */
$app->get('/admin', function() use($app)
{
    return $app['twig']->render('admin.html.twig', array(
        'users' =>  $app['user.repository']->findAll(),
        'days'  =>  $app['day.repository']->getDays(),
    ));
})
->bind('admin');