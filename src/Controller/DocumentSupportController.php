<?php

namespace App\Controller;

use App\Entity\DocumentSupport;
use App\Form\DocumentSupportType;
use App\Repository\DocumentSupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/document/support')]
#[IsGranted('ROLE_USER')]
final class DocumentSupportController extends AbstractController{
    #[Route(name: 'app_document_support_index', methods: ['GET'])]
    public function index(DocumentSupportRepository $documentSupportRepository): Response
    {
        return $this->render('document_support/index.html.twig', [
            'document_supports' => $documentSupportRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_document_support_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $documentSupport = new DocumentSupport();
        $form = $this->createForm(DocumentSupportType::class, $documentSupport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($documentSupport);
            $entityManager->flush();

            return $this->redirectToRoute('app_document_support_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('document_support/new.html.twig', [
            'document_support' => $documentSupport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_document_support_show', methods: ['GET'])]
    public function show(DocumentSupport $documentSupport): Response
    {
        return $this->render('document_support/show.html.twig', [
            'document_support' => $documentSupport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_document_support_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DocumentSupport $documentSupport, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DocumentSupportType::class, $documentSupport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_document_support_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('document_support/edit.html.twig', [
            'document_support' => $documentSupport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_document_support_delete', methods: ['POST'])]
    public function delete(Request $request, DocumentSupport $documentSupport, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$documentSupport->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($documentSupport);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_document_support_index', [], Response::HTTP_SEE_OTHER);
    }
}
