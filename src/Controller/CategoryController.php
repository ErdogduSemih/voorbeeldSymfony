<?php

namespace App\Controller;

use App\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Flex\Response;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category/new", name="new_category")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_MOD')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {
        $category = new Category();

        $form = $this->createFormBuilder($category)
            ->add('name', TextType::class, array(
                'attr' => array('class' => 'form-control')))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('message_list');
        }

        return $this->render('category/new.html.twig', array(
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
        $category = $em->find("\App\Entity\Category", $id);

        if ($category) {
            try {
                $em->remove($category);
                $em->flush();
            } catch (Exception $e) {
                return new Response($e->getMessage(), 500);
            }
            return new Response("Succes deleting category " . $category->getId(), 200);
        } else {
            return new Response('User not found', 500);
        }
    }

    public function getAllUsers()
    {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository(Category::class)->findAll();
    }
}
