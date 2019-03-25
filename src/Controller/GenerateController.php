<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GenerateController extends Controller
{
    /**
     * @Route("/createadmin", name="newadminroute")
     */
    public function newUser(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setUserName('admin');
        $user->setRolesString(
            'ROLE_ADMIN'
        );
        $password = 'admin';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword(
            $user,
            $password
        );
        $user->setPassword($encoded);
        $em->persist($user);
        $em->flush();
        return new Response('Created admin');
    }
}
