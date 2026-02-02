<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
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
        Request $request
    ): Response {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);

        return $this->render('home/index.html.twig', [
            'videos' => $videoRepository->findAll(),
            'videoForm' => $form->createView(),
            'documents' => [],
        ]);
    }
}
