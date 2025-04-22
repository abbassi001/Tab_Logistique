<?php

namespace App\Controller;

use App\Entity\Expediteur;
use App\Form\ExpediteurType;
use App\Repository\ExpediteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expediteur')]
#[IsGranted('ROLE_USER')]

final class ExpediteurController extends AbstractController{
    #[Route(name: 'app_expediteur_index', methods: ['GET'])]
    public function index(ExpediteurRepository $expediteurRepository): Response
    {
        return $this->render('expediteur/index.html.twig', [
            'expediteurs' => $expediteurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_expediteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $expediteur = new Expediteur();
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($expediteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_expediteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expediteur/new.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expediteur_show', methods: ['GET'])]
    public function show(Expediteur $expediteur): Response
    {
        return $this->render('expediteur/show.html.twig', [
            'expediteur' => $expediteur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expediteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expediteur $expediteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExpediteurType::class, $expediteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_expediteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('expediteur/edit.html.twig', [
            'expediteur' => $expediteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expediteur_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Expediteur $expediteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$expediteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($expediteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_expediteur_index', [], Response::HTTP_SEE_OTHER);
    }
}