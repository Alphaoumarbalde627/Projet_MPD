<?php

namespace App\Controller\Admin;

use App\Entity\Temoignage;
use App\Repository\TemoignageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/temoignages')]
#[IsGranted('ROLE_ADMIN')]
final class TemoignageController extends AbstractController
{
    #[Route('', name: 'app_admin_temoignage_index', methods: ['GET'])]
    public function index(TemoignageRepository $temoignageRepository): Response
    {
        return $this->render('admin/temoignage/index.html.twig', [
            'temoignages' => $temoignageRepository->findBy([], ['ordreAffichage' => 'ASC', 'id' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_temoignage_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(Temoignage $temoignage): Response
    {
        return $this->render('admin/temoignage/show.html.twig', [
            'temoignage' => $temoignage,
        ]);
    }

    #[Route('/{id}/valider', name: 'app_admin_temoignage_valider', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function valider(Request $request, Temoignage $temoignage, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('valider_temoignage_' . $temoignage->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de securite invalide.');

            return $this->redirectToRoute('app_admin_temoignage_index');
        }

        $temoignage->setEstActif(!$temoignage->isEstActif());
        $temoignage->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $statut = $temoignage->isEstActif() ? 'valide et publie' : 'desactive';
        $this->addFlash('success', sprintf('Temoignage de %s %s.', $temoignage->getNomClient(), $statut));

        return $this->redirectToRoute('app_admin_temoignage_index');
    }

    #[Route('/{id}/supprimer', name: 'app_admin_temoignage_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function delete(Request $request, Temoignage $temoignage, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_temoignage_' . $temoignage->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de securite invalide.');

            return $this->redirectToRoute('app_admin_temoignage_index');
        }

        $entityManager->remove($temoignage);
        $entityManager->flush();

        $this->addFlash('success', 'Temoignage supprime avec succes.');

        return $this->redirectToRoute('app_admin_temoignage_index');
    }
}
