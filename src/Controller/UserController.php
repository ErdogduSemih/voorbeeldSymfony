<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Flex\Response;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $this->getAllUsers()
        ]);
    }

    /**
     * @Route("/user/new", name="new_user")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('userName', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('password', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('rolesString', ChoiceType::class, [
                'choices' => [
                    'Moderator' => "ROLE_MOD",
                    'Poster' => "ROLE_POSTER",
                ],
                'expanded' => true,
                'attr' => array('class' => 'form-control')
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $plainPassword = $user->getPassword();

            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteUser(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->find("\App\Entity\User", $id);

        if ($user) {
            try {
                $em->remove($user);
                $em->flush();
            } catch (Exception $e) {
                return new Response($e->getMessage(), 500);
            }
            return new Response("Succes deleting user " . $user->getId(), 200);
        } else {
            return new Response('User not found', 500);
        }
    }

    /**
     * @Route("/user/edit/{id}", name="user_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function edit(Request $request, $id, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $user = $this->getDoctrine()->getManager()->find("\App\Entity\User", $id);
        $user->setPassword("");

        $form = $this->createFormBuilder($user)
            ->add('userName', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('password', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('rolesString', ChoiceType::class, [
                'choices' => [
                    'Moderator' => "ROLE_MOD",
                    'Poster' => "ROLE_POSTER",
                ],
                'expanded' => true,
                'attr' => array('class' => 'form-control')
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPassword();

            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function getAllUsers()
    {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository(User::class)->findAll();
    }
}
