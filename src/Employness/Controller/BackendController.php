<?php

namespace Employness\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin Index
 */
$app->get('/admin', function() use($app)
{
    $days = $app['day.repository']->getDays();
    $days_keys = array_keys($days);

    return $app['twig']->render('admin.html.twig', array(
        'users'     =>  $app['user.repository']->findAllJoinCategory(),
        'days'      =>  array_reverse($days),
        'karmas'    =>  $app['karma.repository']->getKarmas($days[$days_keys[0]]['id'], $app['user.repository']),
    ));
})
->bind('admin');

/**
 * AJAX: get categories
 */
$app->post('/admin/getCategories', function(Request $request) use($app)
{
    if (!$request->request->has('user_id') && is_numeric($request->request->get('user_id'))) {
        return false;
    }

    $user_id = $request->request->get('user_id');
    $user = $app['user.repository']->find($user_id);

    return $app['twig']->render('adminGetCategories.html.twig', array(
        'categories'    => $app['category.repository']->findAll(),
        'user'          => $user,
    ));
})
->bind('adminGetCategories');

/**
 * Update user's category
 */
$app->get('/admin/updateCategoryUser/{user_id}/{category_id}', function(Request $request, $user_id, $category_id) use ($app)
{
    $app['user.repository']->update($user_id, array(
        'category_id'    => $category_id ? $category_id : null,
    ));

    $request->getSession()->setFlash('success', $app['translator']->trans('category_updated'));
    return $app->redirect($app['url_generator']->generate('admin'));
})
->bind('adminUpdateCategoryUser');