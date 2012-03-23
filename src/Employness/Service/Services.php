<?php

namespace Employness\Service;

namespace Employness\Form\RateType;

$app['rate.form.service'] = $app->share(function ($app) {

    $form = $app['form.factory']->createBuilder('form');

    return $form->add('karma', 'choice', array(
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
    ->getForm()
    ->remove('_token');
});