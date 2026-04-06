<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Form\Admin\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
final class ProduitController extends AbstractController
{
    #[Route('', name: 'app_admin_produit_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        SousCategorieRepository $sousCategorieRepository,
    ): Response
    {
        $rawSousCategorie = $request->query->all()['sous_categorie'] ?? null;
        $selectedSousCategorieId = (is_scalar($rawSousCategorie) && ctype_digit((string) $rawSousCategorie))
            ? (int) $rawSousCategorie
            : 0;
        $page = $request->query->getInt('page', 1);

        $result = $produitRepository->findAdminPaginatedBySousCategorie(
            $selectedSousCategorieId > 0 ? $selectedSousCategorieId : null,
            $page
        );

        return $this->render('admin/produit/index.html.twig', [
            'produits' => $result['items'],
            'total_produits' => $result['total'],
            'current_page' => $result['page'],
            'total_pages' => $result['total_pages'],
            'selected_sous_categorie_id' => $selectedSousCategorieId,
            'sous_categories' => $sousCategorieRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_produit_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/produits',
                        $newFilename
                    );
                    $produit->setImage('images/produits/' . $newFilename);
                } catch (FileException) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                }
            }

            $produit->setCreatedAt(new \DateTimeImmutable());
            $produit->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_produit_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Produit $produit): Response
    {
        return $this->render('admin/produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_produit_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Produit $produit,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        $form = $this->createForm(ProduitType::class, $produit, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/produits',
                        $newFilename
                    );
                    $produit->setImage('images/produits/' . $newFilename);
                } catch (FileException) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                }
            }

            $produit->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Le produit "' . $produit->getNom() . '" a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_produit_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_produit_' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_admin_produit_index');
    }
}
