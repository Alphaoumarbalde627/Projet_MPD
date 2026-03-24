<?php

namespace App\Controller\Admin;

use App\Entity\SousCategorie;
use App\Form\Admin\SousCategorieType;
use App\Repository\SousCategorieRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/sous-categories')]
#[IsGranted('ROLE_ADMIN')]
final class SousCategorieController extends AbstractController
{
    #[Route('', name: 'app_admin_sous_categorie_index', methods: ['GET'])]
    public function index(SousCategorieRepository $sousCategorieRepository): Response
    {
        return $this->render('admin/sous_categorie/index.html.twig', [
            'sous_categories' => $sousCategorieRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_sous_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sousCategorie = new SousCategorie();
        $form = $this->createForm(SousCategorieType::class, $sousCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $sousCategorie->setCreatedAt($now);
            $sousCategorie->setUpdatedAt($now);

            $entityManager->persist($sousCategorie);
            $entityManager->flush();

            $this->addFlash('success', 'Sous-categorie ajoutee avec succes.');

            return $this->redirectToRoute('app_admin_sous_categorie_index');
        }

        return $this->render('admin/sous_categorie/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_sous_categorie_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(SousCategorie $sousCategorie): Response
    {
        return $this->render('admin/sous_categorie/show.html.twig', [
            'sous_categorie' => $sousCategorie,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_sous_categorie_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(Request $request, SousCategorie $sousCategorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SousCategorieType::class, $sousCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sousCategorie->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Sous-categorie modifiee avec succes.');

            return $this->redirectToRoute('app_admin_sous_categorie_index');
        }

        return $this->render('admin/sous_categorie/edit.html.twig', [
            'sous_categorie' => $sousCategorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_sous_categorie_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(Request $request, SousCategorie $sousCategorie, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_sous_categorie_' . $sousCategorie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('app_admin_sous_categorie_index');
        }

        try {
            $entityManager->remove($sousCategorie);
            $entityManager->flush();
            $this->addFlash('success', 'Sous-categorie supprimee avec succes.');
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Impossible de supprimer cette sous-categorie car des produits y sont rattaches.');
        }

        return $this->redirectToRoute('app_admin_sous_categorie_index');
    }
}
