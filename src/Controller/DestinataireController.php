<?php

namespace App\Controller;

use App\Entity\Destinataire;
use App\Form\DestinataireType;
use App\Repository\DestinataireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Colis;

#[Route('/destinataire')]
#[IsGranted('ROLE_USER')]
final class DestinataireController extends AbstractController{
    #[Route(name: 'app_destinataire_index', methods: ['GET'])]
    public function index(DestinataireRepository $destinataireRepository): Response
    {
        return $this->render('destinataire/index.html.twig', [
            'destinataires' => $destinataireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_destinataire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $destinataire = new Destinataire();
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($destinataire);
            $entityManager->flush();

            return $this->redirectToRoute('app_destinataire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('destinataire/new.html.twig', [
            'destinataire' => $destinataire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_destinataire_show', methods: ['GET'])]
    public function show(Destinataire $destinataire, EntityManagerInterface $entityManager): Response
    {
        // Charger explicitement les colis
        $colisDestinataire = $entityManager->getRepository(Colis::class)->findBy([
            'destinataire' => $destinataire
        ]);
    
        $colisExpediteur = $entityManager->getRepository(Colis::class)->findBy([
            'expediteur' => $destinataire
        ]);
    
        return $this->render('destinataire/show.html.twig', [
            'destinataire' => $destinataire,
            'colis_destinataire' => $colisDestinataire,
            'colis_expediteur' => $colisExpediteur
        ]);
    }

    #[Route('/{id}/edit', name: 'app_destinataire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destinataire $destinataire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DestinataireType::class, $destinataire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_destinataire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('destinataire/edit.html.twig', [
            'destinataire' => $destinataire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_destinataire_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Destinataire $destinataire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$destinataire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($destinataire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_destinataire_index', [], Response::HTTP_SEE_OTHER);
    }
}