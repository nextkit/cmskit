<?php

namespace App\Repository;

use App\Entity\I18N;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method I18N|null find($id, $lockMode = null, $lockVersion = null)
 * @method I18N|null findOneBy(array $criteria, array $orderBy = null)
 * @method I18N[]    findAll()
 * @method I18N[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class I18NRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, I18N::class);
  }

  public function findOneByDefault(): ?I18N
  {
    return $this->createQueryBuilder('i')
      ->andWhere('i.defaultLang = 1')
      ->getQuery()
      ->getOneOrNullResult();
  }
}
