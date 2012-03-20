<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as Exception;

/**
 * Index
 */
$app->get('/', function() use($app)
{
    $days = array();
    $total_karma = 0;
    $total_participants = 0;
    $days_query = $app['db']->query("SELECT * FROM employness_days ORDER BY id DESC LIMIT 30");

    while ($row = $days_query->fetch()) {
        // TODO: create layer that automatically unserialise arrays when fetch db data..
        $participants = unserialize($row['participants']);
        $days[$row['day']] = array(
            'id'            =>  $row['id'],
            'day'           =>  $row['day'],
            'karma'         =>  $row['karma'],
            'participants'  =>  $participants,
        );
        $total_karma += $row['karma'];
        $total_participants += sizeof($participants);
    }

    $yesterday_id = isset($days[date('Y-m-d', strtotime("yesterday"))]['id']) ? $days[date('Y-m-d', strtotime("yesterday"))]['id'] : -1;
    $yesterday_repartiion = $app['db']->query("SELECT day_id, karma, COUNT(*) AS count FROM employness_karma WHERE day_id = ".$yesterday_id." GROUP BY karma");
    $repartition = array();
    while ($row = $yesterday_repartiion->fetch()) {
        $repartition[] = array('karma' => $row['karma'], 'count' => $row['count']);
    }

    $user_best_karma = $app['db']->fetchAssoc("SELECT * FROM employness_users ORDER BY karma/evaluated_days DESC LIMIT 1");
    $avg_karma_users = $app['db']->fetchAssoc("SELECT AVG(karma/evaluated_days) AS avg FROM employness_users");

    return $app['twig']->render('index.html.twig', array(
        'days'                  =>  $days, 
        'repartition'           =>  $repartition,
        'user_best_karma'       =>  $user_best_karma,
        'avg_karma_users'       =>  round($avg_karma_users['avg'], 2),
        'avg_last_days_karma'   =>  $total_participants != 0 ? round($total_karma/$total_participants, 2) : 0,
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

    // let's roll, build the form!
    $form = $app['form.factory']->createBuilder('form');

    $form = $form->add('karma', 'choice', array(
        'label'             => $app['translator']->trans('rate_your_day'),
        'choices'           => array(
            '1' => $app['translator']->trans('rating_1'),
            '2' => $app['translator']->trans('rating_2'),
            '3' => $app['translator']->trans('rating_3'),
            '4' => $app['translator']->trans('rating_4'),
            '5' => $app['translator']->trans('rating_5'),
        ),
        'preferred_choices' => array('3'),
        'required'          => true,
    ))
    ->add('anonymous', 'checkbox', array(
        'label'     =>  $app['translator']->trans('be_anonymous'),
        'required'  => false,
    ))
    ->getForm();

    // then, we can check if a rating is done
    // TODO: uses prepare and eventually rollback for queries..
    if ($request->getMethod() == 'POST') {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $insert = $app['db']->query("INSERT INTO employness_karma SET user_id = ".$user['id'].", day_id = $day_id, karma = ".$data['karma']);
            if (false !== $insert) {
                $new_participants = unserialize($day['participants']);
                $new_participants[] = $data['anonymous'] === true ? 'anonymous' : $user['email'];
                $app['db']->query("UPDATE employness_days SET karma = (karma+".$data['karma']."), participants = '".serialize($new_participants)."' WHERE id = $day_id");
                $app['db']->query("UPDATE employness_users SET evaluated_days = (evaluated_days+1), karma = (karma+".$data['karma'].") WHERE id = ".$user['id']);

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