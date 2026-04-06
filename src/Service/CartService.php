<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class CartService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProduitRepository $produitRepository,
    ) {
    }

    public function addProduct(Produit $produit, int $quantity = 1): void
    {
        if ($quantity < 1) {
            return;
        }

        $cart = $this->getCart();
        $productId = (string) $produit->getId();
        $currentQuantity = $cart[$productId] ?? 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($produit->getQuantiteStock() !== null && $newQuantity > $produit->getQuantiteStock()) {
            $newQuantity = $produit->getQuantiteStock();
        }

        $cart[$productId] = $newQuantity;
        $this->saveCart($cart);
    }

    public function updateProductQuantity(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeProduct($productId);
            return;
        }

        $produit = $this->produitRepository->find($productId);
        if (!$produit) {
            return;
        }

        if ($produit->getQuantiteStock() !== null && $quantity > $produit->getQuantiteStock()) {
            $quantity = $produit->getQuantiteStock();
        }

        $cart = $this->getCart();
        $cart[(string) $productId] = $quantity;
        $this->saveCart($cart);
    }

    public function removeProduct(int $productId): void
    {
        $cart = $this->getCart();
        unset($cart[(string) $productId]);
        $this->saveCart($cart);
    }

    public function clearCart(): void
    {
        $this->saveCart([]);
    }

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    public function getCartItemCount(): int
    {
        return array_sum($this->getCart());
    }

    public function getCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $productId => $quantity) {
            $produit = $this->produitRepository->find((int) $productId);
            if (!$produit) {
                continue;
            }

            $unitPrice = (int) round((float) $produit->getPrix());
            $items[] = [
                'produit' => $produit,
                'quantite' => $quantity,
                'prix_unitaire' => $unitPrice,
                'sous_total' => $unitPrice * $quantity,
            ];
        }

        return $items;
    }

    public function getCartTotal(): int
    {
        $total = 0;

        foreach ($this->getCartItems() as $item) {
            $total += $item['sous_total'];
        }

        return $total;
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }
}
