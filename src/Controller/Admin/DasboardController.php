<?php

namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\ProduitRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DasboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        UserRepository $userRepository,
        ProduitRepository $produitRepository,
        ServiceRepository $serviceRepository,
        CategoryRepository $categoryRepository,
    ): Response
    {
        $stats = [
            'users' => $userRepository->count([]),
            'products' => $produitRepository->count([]),
            'services' => $serviceRepository->count([]),
            'categories' => $categoryRepository->count([]),
        ];

        $lastUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);
        $lastProducts = $produitRepository->findBy([], ['id' => 'DESC'], 5);
        $lastServices = $serviceRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'last_users' => $lastUsers,
            'last_products' => $lastProducts,
            'last_services' => $lastServices,
        ]);
    }
}
