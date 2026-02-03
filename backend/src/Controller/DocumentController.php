<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    #[Route('/document/new', name: 'document_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user !== null) {
                // On privilégie prénom/nom si dispo, sinon email (userIdentifier)
                $autor = trim((string) (($user->getPrenom() ?? '') . ' ' . ($user->getNom() ?? '')));
                if ($autor === '') {
                    $autor = $user->getUserIdentifier();
                }

                // Colonne NOT NULL en BDD
                $document->setAutor($autor);
            }

            $em->persist($document);
            $em->flush();

            $this->addFlash('success', 'Document ajouté avec succès');
        } else {
            // utile si le modal se ferme mais que rien ne s'uploade
            $this->addFlash('error', 'Impossible d\'ajouter le document (formulaire invalide).');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/api/documents', name: 'api_document_list', methods: ['GET'])]
    public function apiList(DocumentRepository $documentRepository): JsonResponse
    {
        return $this->json($documentRepository->findAll(), 200);
    }

    #[Route('/api/documents/{id}', name: 'api_document_show', methods: ['GET'])]
    public function apiShow(Document $document): JsonResponse
    {
        return $this->json($document, 200);
    }
}
