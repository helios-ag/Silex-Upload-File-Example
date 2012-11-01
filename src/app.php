<?php

error_reporting(E_ALL | E_STRICT); 
ini_set('display_errors', 1);
ini_set('log_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

/**  Bootstraping */

use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\FormServiceProvider;

$app = new Silex\Application();

$app->register(new FormServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback'           => 'en'
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/views'
));


$app['translator.messages'] = array();

$app['debug'] = true;

/** App definition */

$app->match('/', function (Request $request) use ($app){

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
                                       'message' => 'File was successfully uploaded!',
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
