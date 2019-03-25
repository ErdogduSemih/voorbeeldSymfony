<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Message;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message_list")
     * @Security("has_role('ROLE_POSTER') or has_role('ROLE_MOD') or has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $messages = $this->getAllMessages();
        $userId = $this->getUser()->getId();
        $userRole = $this->getUser()->getRolesString();

        $filteredMessages = [];
        foreach ($messages as $message) {
            if ($userRole == "ROLE_MOD" || $userRole == "ROLE_ADMIN") {
                array_push($filteredMessages, $message);
            } elseif ($userId == $message->getUserId() && $userRole == "ROLE_POSTER") {
                array_push($filteredMessages, $message);
            }
        }

        return $this->render('message/index.html.twig', [
            'messages' => $filteredMessages
        ]);
    }

    function debug_to_console($data)
    {
        $output = $data;
        if (is_array($output))
            $output = implode(',', $output);

        echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
    }

    /**
     * @Route("/message/search", methods={"GET"}, name="message_search")
     * @param Request $request
     * @return Response
     */
    public function searchMessage(Request $request, PaginatorInterface $paginator)
    {
        $em = $this->getDoctrine()->getManager();
        $search = $request->get("search");

        $query = $em->getRepository(Message::class)->findBySearch($search);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 5);

        return $this->render('default/index.html.twig', [
            'messages' => $pagination
        ]);
    }

    /**
     * @Route("/message/new", name="message_new")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_POSTER') or has_role('ROLE_MOD') or has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        $message = new Message();

        $form = $this->createFormBuilder($message)
            ->add('content', TextareaType::class, array(
                'attr' => array('class' => 'form-control')))
            ->add('category', ChoiceType::class, [
                'choices' => $this->getChoices(),
                'attr' => array('class' => 'form-control')
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $message->setUserId($user->getId());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('message_list');
        }

        return $this->render('message/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/message/edit/{id}", name="message_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_POSTER')")
     */
    public function edit(Request $request, $id)
    {
        $message = new Message();
        $message = $this->getDoctrine()->getManager()->find("\App\Entity\Message", $id);

        $form = $this->createFormBuilder($message)
            ->add('content', TextareaType::class, array('attr' => array('class' => 'form-control')))
            ->add('category', ChoiceType::class, [
                'choices' => $this->getChoices(),
                'required' => true,
                'attr' => array('class' => 'form-control')
            ])->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('message_list');
        }

        return $this->render('user/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/message/delete/{id}", name="message_delete")
     * @Security("has_role('ROLE_POSTER') or has_role('ROLE_MOD') or has_role('ROLE_ADMIN')")
     * @Method({"DELETE"})
     */
    public function deleteMessage(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $message = $em->find("\App\Entity\Message", $id);

        if ($message) {
            try {
                $em->remove($message);
                $em->flush();
            } catch (Exception $e) {
                return new Response($e->getMessage(), 500);
            }
            return new Response("Succes deleting Message " . $message->getId(), 200);
        } else {
            return new Response('Message not found', 500);
        }
    }

    public function getAllMessages()
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository(Message::class)->findAll();
    }

    public function getChoices()
    {
        $choices = array();

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();

        foreach ($categories as $category) {
            $name = $category->getName();
            $choices[$name] = $name;
            //array_push($choices, $name);
        }

        return $choices;
    }

    /**
     * @Route("/message/{id}/comments", name="message_comments")
     * @Method("GET")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showComments(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository(Comment::class)->findAll();

        $filteredComments = array();

        foreach ($comments as $comment) {
            $messageId = $comment->getMessageId();
            if ($messageId == $id) {
                array_push($filteredComments, $comment);
            }
        }

        $message = $em->getRepository(Message::class)->find($id);

        return $this->render('comment/index.html.twig', [
            'message' => $message,
            'comments' => $filteredComments
        ]);
    }


    /**
     * @Route("/message/{id}/comment/new", name="new_message")
     * @Method("POST")
     * @param Request $request
     * @return string
     */
    public function newComment(Request $request, $id)
    {
        $comment = new Comment();
        $content = $request->request->get("content");

        $comment->setContent($content);
        $comment->setMessageId($id);
        $comment->setToken(hash("adler32", $id . $comment->getContent()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        $returnString = "Uw token is: " . $comment->getToken() . " en het ID is: " . $comment->getId();

        return new Response($returnString);
    }
}
