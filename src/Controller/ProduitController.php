<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits')]
final class ProduitController extends AbstractController
{
    #[Route('', name: 'app_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $categoryId = $request->query->getInt('category');
            $rawSousCategorie = $request->query->all()['sous_categorie'] ?? null;
            $sousCategorieId = (is_scalar($rawSousCategorie) && ctype_digit((string) $rawSousCategorie))
                ? (int) $rawSousCategorie
                : 0;
        $searchTerm = trim((string) $request->query->get('q', ''));
        $page = $request->query->getInt('page', 1);

        $result = $produitRepository->findPublicPaginated(
            $categoryId > 0 ? $categoryId : null,
            $sousCategorieId > 0 ? $sousCategorieId : null,
            $searchTerm,
            $page
        );

        return $this->render('produit/index.html.twig', [
            'produits'                  => $result['items'],
            'total_produits'            => $result['total'],
            'current_page'              => $result['page'],
            'total_pages'               => $result['total_pages'],
            'selected_category_id'      => $categoryId,
            'selected_sous_categorie_id' => $sousCategorieId,
            'search_term'               => $searchTerm,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->findOneBy(['id' => $id, 'estActif' => true]);

        if (!$produit) {
            throw $this->createNotFoundException('Ce produit n\'existe pas ou n\'est plus disponible.');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
