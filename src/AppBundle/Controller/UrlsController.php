<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Urls;
use AppBundle\Form\UrlsType;

/**
 * Urls controller.
 *
 * @Route("/admin/urls")
 */
class UrlsController extends Controller
{
    /**
     * Lists all Urls entities.
     *
     * @Route("/", name="admin_urls_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $urls = $em->getRepository('AppBundle:Urls')->findAll();

        return $this->render('urls/index.html.twig', array(
            'urls' => $urls,
        ));
    }

    /**
     * Creates a new Urls entity.
     *
     * @Route("/new", name="admin_urls_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $url = new Urls();
        $form = $this->createForm('AppBundle\Form\UrlsType', $url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($url);
            $em->flush();

            return $this->redirectToRoute('admin_urls_show', array('id' => $url->getId()));
        }

        return $this->render('urls/new.html.twig', array(
            'url' => $url,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Urls entity.
     *
     * @Route("/{id}", name="admin_urls_show")
     * @Method("GET")
     */
    public function showAction(Urls $url)
    {
        $deleteForm = $this->createDeleteForm($url);

        return $this->render('urls/show.html.twig', array(
            'url' => $url,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Urls entity.
     *
     * @Route("/{id}/edit", name="admin_urls_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Urls $url)
    {
        $deleteForm = $this->createDeleteForm($url);
        $editForm = $this->createForm('AppBundle\Form\UrlsType', $url);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($url);
            $em->flush();

            return $this->redirectToRoute('admin_urls_edit', array('id' => $url->getId()));
        }

        return $this->render('urls/edit.html.twig', array(
            'url' => $url,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Urls entity.
     *
     * @Route("/{id}", name="admin_urls_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Urls $url)
    {
        $form = $this->createDeleteForm($url);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($url);
            $em->flush();
        }

        return $this->redirectToRoute('admin_urls_index');
    }

    /**
     * Creates a form to delete a Urls entity.
     *
     * @param Urls $url The Urls entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Urls $url)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_urls_delete', array('id' => $url->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
