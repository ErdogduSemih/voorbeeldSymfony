<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Message;
use http\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/edit/{token}", name="comment_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, $token)
    {
        $comment = $this->getCommentByToken($token);

        $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class, array('attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirect($this->generateUrl("message_comments", array(
                    'id' => $comment->getMessageId())));
        }

        return $this->render('comment/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    private function getCommentByToken($token)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository(Comment::class)->findAll();
        $comment = null;

        foreach ($comments as $search) {
            if ($token == $search->getToken()) {
                $comment = $search;
            }
        }

        return $comment;
    }

    /**
     * @Route("/comment/delete/{token}", name="comment_delete")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository(Comment::class)->findAll();
        $comment = $this->getCommentByToken($token);

        if ($comment) {
            try {
                $em->remove($comment);
                $em->flush();
            } catch (Exception $e) {
                return new Response($e->getMessage(), 500);
            }
            return new Response("Succes deleting comment " . $comment->getId(), 200);
        } else {
            return new Response('comment not found', 500);
        }
    }
}
