<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\TemoignageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        TemoignageRepository $temoignageRepository,
        CategoryRepository $categoryRepository,
    ): Response
    {
        $categoryAliases = [
            'meubles' => ['meubles', 'meuble'],
            'mobilier' => ['mobilier', 'mobilier de bureau'],
            'electromenager' => ['electromenager', 'electro menager', 'electro'],
            'informatique' => ['informatique', 'materiel informatique', 'tech'],
            'services' => ['services', 'service'],
        ];

        $categoryIds = array_fill_keys(array_keys($categoryAliases), null);

        foreach ($categoryRepository->findBy([], ['nom' => 'ASC']) as $category) {
            $categoryName = $this->normalizeCategoryName((string) $category->getNom());

            foreach ($categoryAliases as $key => $aliases) {
                if ($categoryIds[$key] !== null) {
                    continue;
                }

                foreach ($aliases as $alias) {
                    if (str_contains($categoryName, $this->normalizeCategoryName($alias))) {
                        $categoryIds[$key] = $category->getId();
                        break;
                    }
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'temoignages' => $temoignageRepository->findActiveForHome(),
            'category_ids' => $categoryIds,
        ]);
    }

    private function normalizeCategoryName(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            'œ' => 'oe',
            'æ' => 'ae',
            '-' => ' ',
            '_' => ' ',
        ]);
    }
}
