<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VideoController extends AbstractController
{
    #[Route('/videos/new', name: 'video_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_PROF');

        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();

            if (!$user instanceof \App\Entity\User) {
                throw $this->createAccessDeniedException();
            }

            $video->setTeacherFirstName($user->getPrenom());
            $video->setTeacherLastName($user->getNom());

            $em->persist($video);
            $em->flush();
        }

        return $this->redirectToRoute('home');
    }

}
