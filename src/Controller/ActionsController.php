<?php

namespace App\Controller;

use App\Entity\Actions;
use App\Form\ActionsType;
use App\Repository\ActionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actions")
 */
class ActionsController extends AbstractController
{
    /**
     * @Route("/", name="actions_index", methods={"GET"})
     */
    public function index(ActionsRepository $actionsRepository): Response
    {
        return $this->render('actions/index.html.twig', [
            'actions' => $actionsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="actions_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $action = new Actions();
        $form = $this->createForm(ActionsType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($action);
            $entityManager->flush();

            return $this->redirectToRoute('actions_index');
        }

        return $this->render('actions/new.html.twig', [
            'action' => $action,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="actions_show", methods={"GET"})
     */
    public function show(Actions $action): Response
    {
        return $this->render('actions/show.html.twig', [
            'action' => $action,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="actions_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Actions $action): Response
    {
        $form = $this->createForm(ActionsType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('actions_index');
        }

        return $this->render('actions/edit.html.twig', [
            'action' => $action,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="actions_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Actions $action): Response
    {
        $echo = "__".$request->request->get('_token').">>";
        if ($this->isCsrfTokenValid('delete'.$action->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($action);
            $echo = "Deletion";
            //$entityManager->flush();
        }
        return new Response("dell $echo");
        return $this->redirectToRoute('actions_index');
    }
}
