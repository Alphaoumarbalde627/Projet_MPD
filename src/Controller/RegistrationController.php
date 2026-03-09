<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Statut;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        // redirect si déjà authentifié
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // hachage du mot de passe
            $plain = $form->get('plainPassword')->getData();
            $hashed = $passwordHasher->hashPassword($user, $plain);
            $user->setPassword($hashed);

            // rôle utilisateur standard
            $user->setRole(Statut::CLIENT);

            // dates timestamps
            $now = new \DateTimeImmutable();
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte a bien été créé. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
