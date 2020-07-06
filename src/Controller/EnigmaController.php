<?php

namespace App\Controller;

use App\Entity\Enigma;
use App\Form\EnigmaType;
use App\Repository\EnigmaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/enigma")
 */
class EnigmaController extends AbstractController
{
    /**
     * @Route("/", name="enigma_index", methods={"GET"})
     */
    public function index(EnigmaRepository $enigmaRepository): Response
    {
        return $this->render('enigma/index.html.twig', [
            'enigmas' => $enigmaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="enigma_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $enigma = new Enigma();
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($enigma);
            $entityManager->flush();

            return $this->redirectToRoute('enigma_index');
        }

        return $this->render('enigma/new.html.twig', [
            'enigma' => $enigma,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="enigma_show", methods={"GET"})
     */
    public function show(Enigma $enigma): Response
    {
        return $this->render('enigma/show.html.twig', [
            'enigma' => $enigma,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="enigma_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Enigma $enigma): Response
    {
        $form = $this->createForm(EnigmaType::class, $enigma);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('enigma_index');
        }

        return $this->render('enigma/edit.html.twig', [
            'enigma' => $enigma,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="enigma_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Enigma $enigma): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enigma->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($enigma);
            $entityManager->flush();
        }

        return $this->redirectToRoute('enigma_index');
    }
}
