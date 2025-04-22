<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Employe;
use App\Form\RegistrationFormType;
use App\Form\EmployeType;
use App\Security\SecurityControllerAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * RegistrationController handles user registration.
 * It provides a form for admins to create accounts for employees.
 * It also hashes the password and saves the user to the database.
 */

#[IsGranted('ROLE_ADMIN')]
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $employe = new Employe();
        
        // Créer un formulaire combiné ou deux formulaires séparés
        $userForm = $this->createForm(RegistrationFormType::class, $user);
        $employeForm = $this->createForm(EmployeType::class, $employe);
        
        $userForm->handleRequest($request);
        $employeForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid() && $employeForm->isSubmitted() && $employeForm->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $userForm->get('plainPassword')->getData();

            // Encode le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            
            // Définir les champs supplémentaires de l'utilisateur
            $user->setNom($employe->getNom());
            $user->setPrenom($employe->getPrenom());
            $user->setDateCreation(new \DateTime());
            $user->setIsActive(true);
            
            // Par défaut, attribuer le rôle ROLE_USER
            $user->setRoles(['ROLE_USER']);

            // Enregistrer l'utilisateur et l'employé
            $entityManager->persist($user);
            $entityManager->persist($employe);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur et profil employé créés avec succès.');
            return $this->redirectToRoute('app_employe_index');
        }

        return $this->render('registration/register.html.twig', [
            'userForm' => $userForm,
            'employeForm' => $employeForm,
        ]);
    }
}