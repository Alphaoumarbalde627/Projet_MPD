<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Statut;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'app_admin_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{   //route pour la liste des utilisateurs
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['created_at' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }
    //route pour creer un nouvel admin
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $user->setRole(Statut::ADMIN);
            $now = new \DateTimeImmutable();
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $user->setCreatedBy($this->getUser());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Administrateur créé avec succès.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }
     //route pour le detail d un utilisateur 
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('USER_VIEW', $user);

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }
     //route pour supprimer un utilisateur
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('USER_DELETE', $user);

        if (!$this->isCsrfTokenValid('delete-user'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('app_admin_user_index');
    }
}
