<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Index
 */
$app->get('/', function() use($app)
{
    return $app['twig']->render('index.html.twig');
})
->bind('index');

/**
 * login form
 */
$app->match('/login', function(Request $request) use($app)
{
    if ($request->request->has('_email')) {
        $email = strtolower($request->request->get('_email'));
        $sql = "SELECT * FROM employness_users WHERE LOWER(email) = ?";

        if (false === $user = $app['db']->fetchAssoc($sql, array($email))) {
            $request->getSession()->setFlash('error', $app['translator']->trans('username_does_not_exist'));
        } elseif (sha1($request->request->get('_password')) !== $user['password']) {
            $request->getSession()->setFlash('error', $app['translator']->trans('bad_credidentials'));
        } else {
            $request->getSession()->set('user', $user);
            $request->getSession()->setFlash('success', $app['translator']->trans('logged_in'));
            return $app->redirect($request->request->has('_redirect') ? base64_decode($request->request->get('_redirect')) : $app['url_generator']->generate('index'));
        }
    }

    return $app['twig']->render('login.html.twig');
})
->bind('login');

$app->get('/logout', function(Request $request) use($app)
{
    $request->getSession()->set('user', $app['user'] = false);
    $request->getSession()->setFlash('success', $app['translator']->trans('logged_out'));
    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('logout');