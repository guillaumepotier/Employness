<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as Exception;

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
            $request->getSession()->setFlash('error', $app['translator']->trans('email_does_not_exist'));
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

$app->match('/give/karma/{day_id}/{email}/{token}', function(Request $request, $day_id, $email, $token) use($app)
{
    // first, we check that is url is correct, allowed and not already used..
    $sql = "SELECT * FROM employness_users WHERE LOWER(email) = ?";
    $user = $app['db']->fetchAssoc($sql, array($email));

    if (false === $user || sha1($user['id'].$user['token']) !== $token) {
        $request->getSession()->setFlash('error', $app['translator']->trans('bad_credidentials'));
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $sql = "SELECT * FROM employness_days WHERE id = ?";
    $day = $app['db']->fetchAssoc($sql, array((int) $day_id));

    if (false === $day) {
        $request->getSession()->setFlash('error', $app['translator']->trans('day_doesnt_exist'));
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $sql = "SELECT * FROM employness_karma WHERE day_id = ? AND user_id = ?";
    $day_karma = $app['db']->fetchAssoc($sql, array((int) $day_id, (int) $user['id']));

    if (false !== $day_karma) {
        $request->getSession()->setFlash('error', $app['translator']->trans('you_already_rated_this_day'));
        return $app->redirect($app['url_generator']->generate('index'));
    }

    // then, we can check if a rating is done
    // TODO: uses prepare and eventually rollback..
    if ($request->request->has('_karma')) {
        if (in_array($request->request->get('_karma'), array(1, 2, 3, 4, 5))) {
            $insert = $app['db']->query("INSERT INTO employness_karma SET user_id = ".$user['id'].", day_id = $day_id, karma = ".$request->request->get('_karma'));
            if (false !== $insert) {
                $new_participants = unserialize($day['participants']);
                $new_participants[] = $user['email'];
                $app['db']->query("UPDATE employness_days SET karma = (karma+".$request->request->get('_karma')."), participants = '".serialize($new_participants)."' WHERE id = $day_id");
                $app['db']->query("UPDATE employness_users SET evaluated_days = (evaluated_days+1), karma = (karma+".$request->request->get('_karma').") WHERE id = ".$user['id']);

                $request->getSession()->setFlash('success', $app['translator']->trans('successful_rating'));
                return $app->redirect($app['url_generator']->generate('index'));
            } else {
                throw new Exception\HttpException(500, $app['translator']->trans('an_error_occured'));
            }
        } else {
            $request->getSession()->setFlash('error', $app['translator']->trans('wrong_rating_motherfucker'));
        }
    }

    return $app['twig']->render('rate_day.html.twig');
})
->bind('give_karma');