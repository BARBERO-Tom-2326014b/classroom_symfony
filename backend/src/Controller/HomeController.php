<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        // Données mock pour obtenir un rendu identique à ta maquette.
        // On branchera la BDD plus tard.
        $videos = [
            ['title' => 'Introduction à Symfony', 'teacher' => 'Prof. Martin', 'duration' => '45 min'],
            ['title' => 'Security Bundle', 'teacher' => 'Prof. Dubois', 'duration' => '60 min'],
            ['title' => 'API Platform', 'teacher' => 'Prof. Bernard', 'duration' => '50 min'],
            ['title' => 'Doctrine ORM', 'teacher' => 'Prof. Laurent', 'duration' => '55 min'],
        ];

        $documents = [
            ['title' => 'Cours - Chapitre 1', 'type' => 'PDF', 'pages' => '12 pages'],
            ['title' => 'TD - Exercices', 'type' => 'PDF', 'pages' => '8 pages'],
            ['title' => 'Fiche mémo', 'type' => 'PDF', 'pages' => '2 pages'],
            ['title' => 'Annales', 'type' => 'PDF', 'pages' => '20 pages'],
        ];

        // Rendu selon rôle
        if ($this->isGranted('ROLE_PROFESSEUR')) {
            return $this->render('home/professeur.html.twig', [
                'videos' => $videos,
                'documents' => $documents,
            ]);
        }

        // Par défaut: élève
        return $this->render('home/index.html.twig', [
            'videos' => $videos,
            'documents' => $documents,
        ]);
    }
}
