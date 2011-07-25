<?php

require_once __DIR__.'/../vendor/Silex/silex.phar';

/**  Bootstraping */

$app = new Silex\Application;

use Silex\Extension\SymfonyBridgesExtension;
use Silex\Extension\TranslationExtension;
use Silex\Extension\FormExtension;
use Silex\Extension\TwigExtension;

$app->register(new TwigExtension(), array(
    'twig.path'       => array(
	  __DIR__.'/templates',
	  __DIR__.'/../vendor/symfony/src/Symfony/Bridge/Twig/Resources/views/Form'
	),
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));

$app->register(new SymfonyBridgesExtension(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new FormExtension(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new TranslationExtension(), array(
    'translation.class_path' => __DIR__ . '/../vendor/symfony/src',
    'translator.messages' => array()
));

/** App definition */

$app->error(function(Exception $e) use ($app){
    if (!in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
        return $app->redirect('/');
    }
});

$app->match('/', function() use ($app) {
    
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
                /* Make sure the Upload Directory is properly configured and writeable */
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
}, "GET|POST");


return $app;