<?php

namespace App\Twig;

use App\Entity\Service;
use App\Entity\SousCategorie;
use App\Repository\CategoryRepository;
use App\Repository\ServiceRepository;
use App\Repository\SousCategorieRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ClientNavigationExtension extends AbstractExtension
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly SousCategorieRepository $sousCategorieRepository,
        private readonly ServiceRepository $serviceRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('client_navigation_data', [$this, 'getClientNavigationData']),
        ];
    }

    /**
     * Returns categories + subcategories + services to build the public header menu.
     */
    public function getClientNavigationData(): array
    {
        $categories = $this->categoryRepository->findBy([], ['nom' => 'ASC']);

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getId()] = [
                'id' => $category->getId(),
                'nom' => $category->getNom(),
                'sousCategories' => [],
            ];
        }

        $sousCategories = $this->sousCategorieRepository
            ->createQueryBuilder('sc')
            ->leftJoin('sc.category', 'c')
            ->addSelect('c')
            ->orderBy('c.nom', 'ASC')
            ->addOrderBy('sc.nom', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($sousCategories as $sousCategorie) {
            if (!$sousCategorie instanceof SousCategorie) {
                continue;
            }

            $category = $sousCategorie->getCategory();
            if (!$category || !isset($categoryMap[$category->getId()])) {
                continue;
            }

            $categoryMap[$category->getId()]['sousCategories'][] = [
                'id' => $sousCategorie->getId(),
                'nom' => $sousCategorie->getNom(),
            ];
        }

        $services = $this->serviceRepository->findBy([], ['nom' => 'ASC']);

        $serviceItems = array_map(
            static fn (Service $service) => [
                'id' => $service->getId(),
                'nom' => $service->getNom(),
            ],
            $services,
        );

        return [
            'categories' => array_values($categoryMap),
            'services' => $serviceItems,
        ];
    }
}