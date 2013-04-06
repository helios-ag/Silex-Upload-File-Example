<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->match('/', function (Request $request) use ($app){

    $form = $app['form.factory']->createBuilder('form')
        ->add('FileUpload', 'file')
        ->getForm();

    $request = $app['request'];

    if ($request->isMethod('POST'))
    {
        $form->bind($request);
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

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});