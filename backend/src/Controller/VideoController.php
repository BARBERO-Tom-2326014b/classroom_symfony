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

            // 👤 Infos du prof connecté
            $user = $this->getUser();

            // ⚠️ adapte si ton User n’a pas nom/prenom
            $video->setTeacherFirstName($user->getUserIdentifier());
            $video->setTeacherLastName('');

            // 🔥 C’EST ICI QUE VICH S’EXÉCUTE
            $em->persist($video);
            $em->flush();

            $this->addFlash('success', 'Vidéo ajoutée avec succès');
        }

        return $this->redirectToRoute('home');
    }
}
