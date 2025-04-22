<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 *
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * Trouve un employé par son adresse email
     */
    public function findOneByEmail(string $email): ?Employe
    {
        return $this->findOneBy(['email' => $email]);
    }
    
    /**
     * Trouve un employé associé à un utilisateur (soit par relation directe, soit par email)
     */
    public function findOneByUser($user): ?Employe
    {
        // Si l'utilisateur a déjà un employé associé
        if (method_exists($user, 'getEmploye') && $user->getEmploye()) {
            return $user->getEmploye();
        }
        
        // Sinon, on cherche par email
        return $this->findOneByEmail($user->getEmail());
    }
}