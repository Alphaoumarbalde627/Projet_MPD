<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        // Si l'utilisateur n'est pas authentifié, redirection vers les produits
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Utilisateur connecté : afficher le panier
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'])]
    public function add(int $id, ProduitRepository $produitRepository, Request $request): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter des produits au panier.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le produit
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // TODO: Logique d'ajout au panier
        $this->addFlash('success', 'Produit ajouté au panier avec succès.');

        // Rediriger vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_home'));
    }
}