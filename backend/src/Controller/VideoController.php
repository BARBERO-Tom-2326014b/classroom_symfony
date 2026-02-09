<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        // Cas fréquent: dépassement post_max_size/upload_max_filesize => pas de fichier dans la requête, sans erreur côté form
        if ($request->isMethod('POST') && ($request->files->count() === 0)) {
            $this->addFlash(
                'danger',
                'Upload impossible : la requête est vide. Vérifie la taille de la vidéo et les limites serveur (PHP post_max_size / upload_max_filesize).'
            );

            return $this->redirectToRoute('home');
        }

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

    #[Route('/api/videos', name: 'api_video_list', methods: ['GET'])]
    public function apiList(VideoRepository $videoRepository): JsonResponse
    {
        $videos = $videoRepository->findAll();

        return $this->json($videos, 200);
    }

    #[Route('/video/{id}', name: 'video_show', methods: ['GET'])]
    public function show(Video $video): JsonResponse
    {
        return $this->json($video, 200);
    }

    #[Route('/api/videos/{id}', name: 'api_video_show', methods: ['GET'])]
    public function apiShow(Video $video): JsonResponse
    {
        return $this->json($video, 200);
    }
}
