<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Testimonial;
use AppBundle\Form\TestimonialType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage testimonial in the backend.
 * @Route("/admin/testimonial")
 * @Security("has_role('ROLE_ADMIN')")
 */

class TestimonialController extends Controller
{
    /**
     * Lists all the testimonial entities.
     *
     * @Route("/", name="admin_testimonial_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $testimonial = $em->getRepository(Testimonial::class)->findAll();

        return $this->render('admin/testimonial/index.html.twig', ['objects' => $testimonial]);
    }

    /**
     * @Route("/new", name="admin_testimonial_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $testimonial = new Testimonial();
        $testimonial->setAuthor($this->getUser());

        $form = $this->createForm(TestimonialType::class, $testimonial);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($testimonial);
            $em->flush();

            $this->addFlash('success', 'action.created_successfully');

            return $this->redirectToRoute('admin_testimonial_index');
        }

        return $this->render('admin/testimonial/new.html.twig', [
            'object' => $testimonial,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_testimonial_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Testimonial $testimonial)
    {
        $form = $this->createForm(TestimonialType::class, $testimonial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'action.updated_successfully');

            return $this->redirectToRoute('admin_testimonial_index');
        }

        return $this->render('admin/testimonial/edit.html.twig', [
            'object' => $testimonial,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a testimonial entity.
     *
     * @Route("/{id}/delete", name="admin_testimonial_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Testimonial $testimonial)
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_testimonial_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($testimonial);
        $em->flush();

        $this->addFlash('success', 'action.deleted_successfully');

        return $this->redirectToRoute('admin_testimonial_index');
    }
}