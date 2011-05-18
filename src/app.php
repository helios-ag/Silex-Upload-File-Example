<?php

require_once __DIR__.'/../vendor/Silex/silex.phar';

/**  Bootstraping */

$app = new Silex\Application;

$app->register(new \Silex\Extension\TwigExtension(), array(
    'twig.path' => __DIR__.'/templates',
    'twig.class_path' => __DIR__.'/../vendor/Twig/lib',
));

$app->register(new \Silex\Extension\SymfonyBridgesExtension(), array(
   'symfony_bridges.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new \Silex\Extension\FormExtension(), array(
    'form.class_path' => __DIR__ . '/../vendor/symfony/src'
));

$app->register(new \Silex\Extension\TranslationExtension(), array(
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
                $filename = $files['FileUpload']['file']->getOriginalName();
                $files['FileUpload']['file']->move($path,$filename);
                               
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