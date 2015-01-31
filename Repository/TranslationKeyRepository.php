<?php
/**
 * Created by PhpStorm.
 * User: tim
 * Date: 30-1-15
 * Time: 23:07
 */

namespace ConnectSB\TranslationBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class TranslationKeyRepository extends EntityRepository

{
    public function getTranslationKeysFromDatabase($databaseTranslationsEntityString, $entity)
    {
        $result = $this
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.' . strtolower(explode(':', $databaseTranslationsEntityString)[1]), 'c')
            ->where('c.id = :entity')
            ->setParameter('entity', $entity)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }


}