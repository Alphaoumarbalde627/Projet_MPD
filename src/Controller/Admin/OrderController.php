<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Enum\StatutCom;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commandes')]
#[IsGranted('ROLE_ADMIN')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly CommandeRepository $commandeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'admin_order_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $status = $request->query->get('status');

        $queryBuilder = $this->commandeRepository->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('c.ligneCommandes', 'lc')
            ->leftJoin('lc.produit', 'p')
            ->addSelect('u', 'lc', 'p')
            ->orderBy('c.created_at', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('c.statut = :status')
                ->setParameter('status', StatutCom::from($status));
        }

        $paginator = $queryBuilder->getQuery()->getResult();

        // Simple pagination (on peut améliorer avec KnpPaginator plus tard)
        $limit = 5;
        $offset = ($page - 1) * $limit;
        $total = count($paginator);
        $orders = array_slice($paginator, $offset, $limit);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'selected_status' => $status,
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $commande,
        ]);
    }

    #[Route('/{id}/statut', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Commande $commande): Response
    {
        $newStatus = $request->request->get('status');

        if (!in_array($newStatus, array_column(StatutCom::cases(), 'value'))) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
        }

        $commande->setStatut(StatutCom::from($newStatus));
        $commande->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->addFlash('success', 'Statut de la commande mis à jour.');

        return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'admin_order_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_order_index');
        }

        $this->entityManager->remove($commande);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande supprimée.');

        return $this->redirectToRoute('admin_order_index');
    }
}