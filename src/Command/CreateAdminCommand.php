<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\Statut;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un administrateur depuis la console.'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création du premier administrateur');

        $email = trim((string) $io->ask('Email de l\'administrateur'));
        if ($email === '') {
            $io->error('L\'email est obligatoire.');

            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error('Un utilisateur avec cet email existe déjà.');

            return Command::FAILURE;
        }

        $prenom = trim((string) $io->ask('Prénom'));
        $nom = trim((string) $io->ask('Nom'));
        $tel = trim((string) $io->ask('Téléphone (ex: +224...)'));

        if ($prenom === '' || $nom === '' || $tel === '') {
            $io->error('Prénom, nom et téléphone sont obligatoires.');

            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['tel' => $tel])) {
            $io->error('Un utilisateur avec ce numéro de téléphone existe déjà.');

            return Command::FAILURE;
        }

        $password = (string) $io->askHidden('Mot de passe', function (?string $value): string {
            $clean = trim((string) $value);

            if ($clean === '') {
                throw new \RuntimeException('Le mot de passe est obligatoire.');
            }

            if (mb_strlen($clean) < 8) {
                throw new \RuntimeException('Le mot de passe doit contenir au moins 8 caractères.');
            }

            return $clean;
        });

        $passwordConfirm = (string) $io->askHidden('Confirmer le mot de passe');
        if ($password !== $passwordConfirm) {
            $io->error('Les mots de passe ne correspondent pas.');

            return Command::FAILURE;
        }

        $admin = new User();
        $admin->setEmail($email);
        $admin->setPrenom($prenom);
        $admin->setNom($nom);
        $admin->setTel($tel);
        $admin->setRole(Statut::ADMIN);

        $now = new \DateTimeImmutable();
        $admin->setCreatedAt($now);
        $admin->setUpdatedAt($now);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Administrateur créé avec succès : %s', $admin->getEmail()));
        $io->writeln('Vous pouvez maintenant vous connecter avec ce compte sur le formulaire standard.');

        return Command::SUCCESS;
    }
}
