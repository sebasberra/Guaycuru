<?php

namespace Pruebas\SeguridadBundle\Controller;

use Pruebas\SeguridadBundle\Form\UserType;
use Pruebas\SeguridadBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistracionController extends Controller
{
    
    public function registroAction(Request $request)
    {
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            #return $this->redirectToRoute('replace_with_some_route');
            return $this->render(
                'SeguridadBundle:registracion:registrado.html.twig');
        }

        return $this->render(
            'SeguridadBundle:registracion:registrar.html.twig',
            array('form' => $form->createView())
        );
    }
}

