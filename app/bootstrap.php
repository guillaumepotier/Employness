<?php

require_once __DIR__.'/autoload.php';

use Employness\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as Exception;

$app = new Application();
$config = require __DIR__.'/config/config.php';

// console env gestion
if (isset($_SERVER['ENV']) && in_array($_SERVER['ENV'], array('dev', 'test', 'prod'))) {
    $config['env'] = $_SERVER['ENV'];
}
$app['debug'] = $config['env'] == 'prod' ? false : true;
$app['host'] = $config['host'];
$app['mailer.email'] = $config['mailer']['username'];

/**
*   Load Extensions / Services
**/
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile'       => __DIR__.'/logs/'.$config['env'].'.log',
    'monolog.class_path'    => __DIR__.'/../vendor/monolog/src',
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => array(
        __DIR__.'/../src/Employness/Resources/views',
        __DIR__.'/../vendor/Symfony/Bridge/Twig/Resources/views/Form',
    ),
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));
$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));
$app->register(new Silex\Provider\FormServiceProvider(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback'           => 'en',
    'translation.class_path'    => __DIR__.'/../vendor/symfony/src',
));
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
    'swiftmailer.options'       => $config['mailer'],
    'swiftmailer.class_path'    => __DIR__.'/../vendor/swiftmailer/lib/classes',
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'            => $config['db'][$config['env']],
    'db.dbal.class_path'    => __DIR__.'/../vendor/dbal/lib',
    'db.common.class_path'  => __DIR__.'/../vendor/common/lib',
));

// be sure not to acccess db and mailer config elsewhere
unset($config['db']);
unset($config['mailer']);

/**
*   Load DB Repositories
**/
$app['user.repository'] = function() use($app) {
    return new \Employness\Repositories\UserRepository($app['db'], 'employness_users', array('id'));
};

$app['day.repository'] = function() use($app) {
    return new \Employness\Repositories\DayRepository($app['db'], 'employness_days', array('id'));
};

$app['karma.repository'] = function() use($app) {
    return new \Employness\Repositories\KarmaRepository($app['db'], 'employness_karma', array('id'));
};

/**
*   Load Translations
**/
$app['translator.messages'] = require_once __DIR__.'/../src/Employness/Resources/translations/translations.php';
if (!isset($app['translator.messages'][$config['locale']])) {
    die('You must provide a valid locale in your config file');
}
$app['locale'] = $config['locale'];

/**
*   Load Controllers
**/
require __DIR__.'/../src/Employness/Controller/FrontendController.php';
require __DIR__.'/../src/Employness/Controller/BackendController.php';

/**
*   Usermanagement & session
**/
$app->before(function(Request $request) use ($app)
{
    // add here some Twig extensions. twig key seems unavailable outside a controller..
    $app['twig']->addExtension(new \Twig_Extensions_Extension_Text());
    $app['twig']->addExtension(new Employness\Twig\AssetsExtension(str_replace('/index.php', '', $_SERVER['PHP_SELF']).'/assets'));

    // User management
    $app['session']->start();
    $app['user'] = $request->getSession()->get('user', false);

    if (preg_match('/^admin/', $request->attributes->get('_route'))) {
        $sql = "SELECT * FROM employness_users WHERE LOWER(email) = ? AND password = ?";

        if (false === $app['user'] || false === $user = $app['db']->fetchAssoc($sql, array($app['user']['email'], $app['user']['password']))) {
            $request->getSession()->setFlash('warning', $app['translator']->trans('you_must_be_logged'));
            return $app->redirect($app['url_generator']->generate(
                'login',
                array('redirect' => base64_encode($app['url_generator']->generate('admin')))
            ));
        } elseif ($user['admin'] == 0) {
            throw new Exception\AccessDeniedHttpException('must_be_admin');
        }
    }
});

/**
*   Custom error pages
**/
$app->error(function (\Exception $e) use ($app) 
{
    if ($e instanceof Exception\NotFoundHttpException) {
        return $app['twig']->render('Default/error.html.twig', array('code' => 404, 'message' => $e->getMessage()));
    } elseif ($e instanceof Exception\AccessDeniedHttpException) {
        return $app['twig']->render('Default/error.html.twig', array('code' => 403, 'message' => $e->getMessage()));
    }

    $code = ($e instanceof HttpException) ? $e->getStatusCode() : 500;
    return $app['twig']->render('Default/error.html.twig', array('code' => $code, 'message' => $e->getMessage()));
});

// use cache in prod env
return $app;