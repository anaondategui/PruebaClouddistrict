<?php

namespace App\Repository;

use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * @method Producto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Producto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Producto[]    findAll()
 * @method Producto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    // /**
    //  * @return Producto[] Returns an array of Producto objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findByCategoria($nombreAbuscar)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQueryBuilder();
        $query->select('p')
        ->from('App:Producto', 'p')
        ->leftJoin('p.categoria', 'c')
        ->where('c.nombre like :nombreAbuscar')
        ->setParameter('nombreAbuscar', '%' .$nombreAbuscar. '%');

        return $query->getQuery()->getResult();
    }
    public function findByName($nombreAbuscar)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Producto p
            WHERE p.nombre LIKE :nombreAbuscar
            ORDER BY p.nombre ASC'
        )->setParameter('nombreAbuscar', '%' .$nombreAbuscar. '%');

        return $query->getResult();

    }

    public function findByPrice($priceme,$pricema): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Producto p
            WHERE p.precio > :pricema
            AND p.precio < :priceme
            ORDER BY p.precio ASC'
        )->setParameters(array('pricema'=>$pricema,'priceme'=>$priceme));

        return $query->getResult();
    }


/*
    public function findOneBySomeField($value): ?Producto
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function paginate($dql, $page = 1, $limit = 3)
    {
        $dql->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }


}
