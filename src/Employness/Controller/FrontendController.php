<?php

namespace Employness\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as Exception;

/**
 * Index
 */
$app->get('/', function() use($app)
{
    $days = $app['day.repository']->getDaysByCategory();
    $yesterday_id = isset($days[date('Y-m-d', strtotime("yesterday"))]['id']) ? $days[date('Y-m-d', strtotime("yesterday"))]['id'] : -1;
    $categories = array();
    foreach ($days as $day) {
        $categories = array_merge($categories, array_keys($day['categories']));
    }
    $categories = array_unique($categories);

    return $app['twig']->render('index.html.twig', array(
        'days'                  =>  $days,
        'categories'            =>  $categories,
        'avg_last_days_karma'   =>  $app['day.repository']->getAvgKarma(),
        'avg_karma_users'       =>  $app['user.repository']->getAvgUsersKarma(),
        'user_best_karma'       =>  $app['user.repository']->getUserWithBestKarma(),
        'repartition'           =>  $app['karma.repository']->getKarmaRepartitionForDayWithId($yesterday_id),
    ));
})
->bind('index');

/**
 * login form
 */
$app->match('/login', function(Request $request) use($app)
{
    if ($request->request->has('_email')) {
        $email = strtolower($request->request->get('_email'));

        if (false === $user = $app['user.repository']->getUser($email)) {
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

/**
 * logout action
 */
$app->get('/logout', function(Request $request) use($app)
{
    $request->getSession()->set('user', $app['user'] = false);
    $request->getSession()->setFlash('success', $app['translator']->trans('logged_out'));
    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('logout');

/**
 * rate your day action
 */
$app->match('/give/karma/{day_id}/{email}/{token}', function(Request $request, $day_id, $email, $token) use($app)
{
    // first, we check that is url is correct, allowed and not already used..
    $user = $app['user.repository']->getUser($email);
    if (false === $user || sha1($user['id'].$user['token']) !== $token) {
        throw new Exception\AccessDeniedHttpException($app['translator']->trans('bad_credidentials'));
    }

    $day = $app['day.repository']->find($day_id);
    if (false === $day) {
        throw new Exception\AccessDeniedHttpException($app['translator']->trans('day_doesnt_exist'));
    }

    if (false !== $app['karma.repository']->findOneBy(array('day_id' => $day_id, 'user_id' => $user['id']))) {
        throw new Exception\AccessDeniedHttpException($app['translator']->trans('you_already_rated_this_day'));
    }

    // let's roll, build the form!
    $form = $app['rate.form.service'];

    // then, we can check if a rating is done
    // TODO: uses prepare and eventually rollback for queries..
    if ($request->getMethod() == 'POST') {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (false !== $app['karma.repository']->insert(array('user_id' => $user['id'], 'day_id' => $day_id, 'karma' => $data['karma']))) {
                $new_participants = unserialize($day['participants']);
                $new_participants[] = $data['anonymous'] === true ? 'anonymous' : $user['email'];

                $app['day.repository']->update($day_id, array(
                    'karma' => $day['karma']+$data['karma'],
                    'participants' => serialize($new_participants),
                ));
                $app['user.repository']->update($user['id'], array(
                    'evaluated_days' => $user['evaluated_days']+1,
                    'karma' => $user['karma']+$data['karma'],
                ));

                $request->getSession()->setFlash('success', $app['translator']->trans('successful_rating'));
                return $app->redirect($app['url_generator']->generate('index'));
            } else {
                throw new Exception\HttpException(500, $app['translator']->trans('an_error_occured'));
            }
        } else {
            $request->getSession()->setFlash('error', $app['translator']->trans('wrong_rating_motherfucker'));
        }
    }

    return $app['twig']->render('rate_day.html.twig', array('form' => $form->createView()));
})
->bind('give_karma');