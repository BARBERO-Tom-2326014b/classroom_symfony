<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\VideoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class VideoController extends AbstractController
{
    #[Route('/video/new', name: 'video_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 🔒 Sécurité
        $this->denyAccessUnlessGranted('ROLE_PROFESSEUR');

        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user = $this->getUser();


            $video->setTeacherFirstName($user->getUserIdentifier());
            $video->setTeacherLastName('');


            $em->persist($video);
            $em->flush();

            $this->addFlash('success', 'Vidéo ajoutée avec succès');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/video', name: 'video_list', methods: ['GET'])]
    public function list(VideoRepository $videoRepository): JsonResponse
    {
        $videos = $videoRepository->findAll();

        return $this->json($videos, 200);
    }

    #[Route('/video/{id}', name: 'video_show', methods: ['GET'])]
    public function show(Video $video): JsonResponse
    {
        return $this->json($video, 200);
    }

}
