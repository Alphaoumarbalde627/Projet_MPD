<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Enum\StatutCom;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_USER')]
final class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ProduitRepository $produitRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['GET'])]
    public function add(int $id): Response
    {
        $produit = $this->produitRepository->findOneBy(['id' => $id, 'estActif' => true]);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $this->cartService->addProduct($produit);

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getCartTotal();

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/mettre-a-jour/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, int $id): Response
    {
        $quantity = $request->request->getInt('quantity', 1);
        $this->cartService->updateProductQuantity($id, $quantity);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/supprimer/{id}', name: 'app_cart_remove', methods: ['GET'])]
    public function remove(int $id): Response
    {
        $this->cartService->removeProduct($id);

        $this->addFlash('success', 'Produit retiré du panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/vider', name: 'app_cart_clear', methods: ['GET'])]
    public function clear(): Response
    {
        $this->cartService->clearCart();

        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/valider-commande', name: 'app_cart_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request): Response
    {
        $cartItems = $this->cartService->getCartItems();
        if (empty($cartItems)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();
        $total = $this->cartService->getCartTotal();

        if ($request->isMethod('POST')) {
            // Créer la commande
            $commande = new Commande();
            $commande->setUser($user);
            $commande->setMontantTotal((string) $total);
            $commande->setStatut(StatutCom::EN_ATTENTE);
            $commande->setCreatedAt(new \DateTimeImmutable());
            $commande->setUpdatedAt(new \DateTimeImmutable());

            foreach ($cartItems as $item) {
                $ligne = new LigneCommande();
                $ligne->setProduit($item['produit']);
                $ligne->setQuantite($item['quantite']);
                $ligne->setPrix((string) $item['prix_unitaire']);
                $ligne->setCreatedAt(new \DateTimeImmutable());
                $ligne->setUpdatedAt(new \DateTimeImmutable());
                $commande->addLigneCommande($ligne);
            }

            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            // Vider le panier
            $this->cartService->clearCart();

            // Notifier les admins
            $this->notifyAdmins($commande);

            $this->addFlash('success', 'Commande validée avec succès. Un administrateur vous contactera bientôt.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('cart/checkout.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    private function notifyAdmins(Commande $commande): void
    {
        $admins = $this->userRepository->findBy(['role' => \App\Enum\Statut::ADMIN]);

        foreach ($admins as $admin) {
            $email = (new TemplatedEmail())
                ->from('noreply@galeriempd.com')
                ->to($admin->getEmail())
                ->subject('Nouvelle commande reçue')
                ->htmlTemplate('emails/order_notification.html.twig')
                ->context([
                    'commande' => $commande,
                    'client' => $commande->getUser(),
                ]);

            $this->mailer->send($email);
        }
    }
}