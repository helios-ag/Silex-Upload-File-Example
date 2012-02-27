<?php

require_once __DIR__.'/../vendor/Silex/silex.phar';

/**  Bootstraping */

use Silex\Provider\FormServiceProvider;

$app = new Silex\Application();

$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
    'symfony_bridges.class_path'  => __DIR__.'/../vendor/symfony/src',
));

$app->register(new Silex\Provider\FormServiceProvider(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback'           => 'en',
    'translation.class_path'    => __DIR__.'/../vendor/symfony/src',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/views',
    'twig.class_path' => __DIR__.'/../vendor/twig/lib',
));


$app['translator.messages'] = array();

$app['debug'] = true;

/** App definition */

$app->match('/', function(Silex\Application $app){

    $form = $app['form.factory']->createBuilder('form')
        ->add('FileUpload', 'file')
        ->getForm();

    $request = $app['request'];

        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
            if ($form->isValid())
            {
                    $files = $request->files->get($form->getName());
                    /* Make sure that Upload Directory is properly configured and writable */
                    $path = __DIR__.'/../web/upload/';
                    $filename = $files['FileUpload']->getClientOriginalName();
                    $files['FileUpload']->move($path,$filename);

            }
            return $app['twig']->render('index.html.twig', array(
                                       'message' => 'File Uploaded',
                                       'form' => $form->createView()
                                        ));

        }
    return $app['twig']->render('index.html.twig', array(
            'message' => 'Upload a file',
            'form' => $form->createView()
        )
    );
}, 'GET|POST');


return $app;