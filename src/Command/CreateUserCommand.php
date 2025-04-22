<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $io->ask('Email');
        $password = $io->askHidden('Password');
        $nom = $io->ask('Nom');
        $prenom = $io->ask('Prénom');
        $isAdmin = $io->confirm('Make this user an admin?');
        
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
        $user->setDateCreation(new \DateTime());
        $user->setIsActive(true);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $io->success('User created successfully.');
        
        return Command::SUCCESS;
    }
}