<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Video;
use App\Form\DocumentType;
use App\Form\VideoType;
use App\Repository\DocumentRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('home');
    }

    #[Route('/home', name: 'home')]
    public function index(
        VideoRepository $videoRepository,
        DocumentRepository $documentRepository,
        Request $request
    ): Response {
        $video = new Video();
        $videoForm = $this->createForm(VideoType::class, $video);

        $document = new Document();
        $documentForm = $this->createForm(DocumentType::class, $document);

        return $this->render('home/index.html.twig', [
            'videos' => $videoRepository->findAll(),
            'videoForm' => $videoForm->createView(),
            'documents' => $documentRepository->findAll(),
            'documentForm' => $documentForm->createView(),
        ]);
    }
}
