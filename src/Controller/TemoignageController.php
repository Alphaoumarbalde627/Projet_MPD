<?php

namespace App\Controller;

use App\Entity\Temoignage;
use App\Form\TemoignageClientType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/temoignage')]
final class TemoignageController extends AbstractController
{
    #[Route('/nouveau/{id}', name: 'app_temoignage_new', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function new(
        int $id,
        Request $request,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
    ): Response {
        $produit = $produitRepository->findOneBy(['id' => $id, 'estActif' => true]);

        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $temoignage = new Temoignage();
        $form = $this->createForm(TemoignageClientType::class, $temoignage);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Collecter les erreurs détaillées
                $errors = [];
                foreach ($form as $key => $field) {
                    if ($field->getErrors(true)->count() > 0) {
                        foreach ($field->getErrors(true) as $error) {
                            $errors[$key] = $error->getMessage();
                        }
                    }
                }
                // Si pas d'erreurs détaillées, afficher l'erreur générique du formulaire
                if (empty($errors) && $form->getErrors(true)->count() > 0) {
                    foreach ($form->getErrors(true) as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                } else {
                    foreach ($errors as $field => $message) {
                        $this->addFlash('error', "❌ $field: $message");
                    }
                }
            } elseif ($form->isSubmitted() && $form->isValid()) {
                $videoFile = $form->get('videoFile')->getData();

                if ($videoFile) {
                    $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/videos/temoignages';

                    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
                        $this->addFlash('error', 'Le dossier de stockage des videos est introuvable.');

                        return $this->render('temoignage/new.html.twig', [
                            'form' => $form,
                            'produit' => $produit,
                        ]);
                    }

                    $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . ($videoFile->guessExtension() ?: 'mp4');

                    try {
                        $videoFile->move($targetDirectory, $newFilename);
                    } catch (FileException) {
                        $this->addFlash('error', 'Une erreur est survenue pendant l\'upload de la video.');

                        return $this->render('temoignage/new.html.twig', [
                            'form' => $form,
                            'produit' => $produit,
                        ]);
                    }

                    $now = new \DateTimeImmutable();
                    $temoignage->setProduit($produit);
                    $temoignage->setVideo('videos/temoignages/' . $newFilename);
                    $temoignage->setEstActif(false); // En attente de validation par l'admin
                    $temoignage->setOrdreAffichage(0);
                    $temoignage->setCreatedAt($now);
                    $temoignage->setUpdatedAt($now);

                    $entityManager->persist($temoignage);
                    $entityManager->flush();

                    $this->addFlash('success', 'Merci ! Votre temoignage a bien ete recu. Il sera visible apres validation par notre equipe.');

                    return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
                } else {
                    $this->addFlash('error', 'Veuillez selectionner une video.');
                }
            }
        }

        return $this->render('temoignage/new.html.twig', [
            'form' => $form,
            'produit' => $produit,
        ]);
    }
}
