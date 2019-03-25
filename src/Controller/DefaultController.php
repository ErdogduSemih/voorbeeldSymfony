<?php

namespace App\Controller;

use App\Entity\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homeroute")
     */
    public function index(Request $request)
    {
        $response = new Response('', 200);


        $entityManager = $this->getDoctrine()->getManager();
        $messagesRepository = $entityManager->getRepository(Message::class);

        // Find all the data on the Appointments table, filter your query as you need
        $allMessagesQuery = $messagesRepository->createQueryBuilder('p')
            ->getQuery();

        $paginator  = $this->get('knp_paginator');

        // Paginate the results of the query
        $messages = $paginator->paginate(
        // Doctrine Query, not results
            $allMessagesQuery,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        return $this->render(
            'default/index.html.twig',
            ['messages' => $messages],
            $response
        );
    }

    /**
     * @Route("/adminpage", name="adminroute")
     */
    public function admin(Request $request)
    {
        return new Response("adminpage");
    }


    /**
     * @Route("/userpage", name="userroute")
     */
    public function user(Request $request)
    {
        return new Response("userpage<br/>");
    }

    /**
     * @Route("/quit", name="quitroute")
     */
    public function quit(Request $request)
    {
        header("HTTP/1.1 401 Access Denied");
        header("WWW-Authenticate: " .
            "Basic realm=\"localhost:8000/\"");
        header("Content-Length: 0");
        return new Response(null, 401);
    }

    private function getAllMessages()
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository(Message::class)->findAll();
    }
}
