<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    #[Route('/apropos', name: 'app_about')]
    public function index(): Response
    {
        return $this->render('apropos/about.html.twig', [
            'page_title' => 'À propos - Galerie MPD',
        ]);
    }
}
