<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\Datum;
use App\Enum\DatumTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

class DatumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Datum::class);
    }

    public function findAllItemsLabelsInCollection(Collection $collection, array $types = []): array
    {
        return $this
            ->createQueryBuilder('datum')
            ->leftJoin('datum.item', 'item')
            ->select('datum.label, datum.type')
            ->distinct()
            ->where('item.collection = :collection')
            ->andWhere('datum.type IN (:types)')
            ->setParameter('collection', $collection->getId())
            ->setParameter('types', $types)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findAllChildrenLabelsInCollection(?Collection $collection, array $types = []): array
    {
        $qb = $this
            ->createQueryBuilder('datum')
            ->select('datum.label, datum.type')
            ->distinct()
            ->where('datum.type IN (:types)')
            ->setParameter('types', $types)
        ;

        if ($collection instanceof Collection) {
            $qb
                ->join('datum.collection', 'collection', 'WITH', 'collection.parent = :parent')
                ->setParameter('parent', $collection->getId())
            ;
        } else {
            $qb
                ->join('datum.collection', 'collection', 'WITH', 'collection.parent IS NULL')
            ;
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function computePricesForCollection(Collection $collection, string $visibility): array
    {
        $id = $collection->getId();
        $type = DatumTypeEnum::TYPE_PRICE;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('label', 'label');
        $rsm->addScalarResult('currency', 'currency');
        $rsm->addScalarResult('value', 'value');
        $rsm->addScalarResult('quantity', 'quantity');

        $sql = "
            SELECT datum.label AS label, datum.currency AS currency, datum.value AS value, item.quantity AS quantity
            FROM koi_datum datum
            JOIN koi_item item ON datum.item_id = item.id AND item.collection_id = '{$id}'
            WHERE datum.type = '{$type}'
            AND datum.visibility = '{$visibility}'
        ";

        $prices = [];
        $results = $this->getEntityManager()->createNativeQuery($sql, $rsm)->getArrayResult();
        foreach ($results as $result) {
            if (!isset($prices[$result['label']][$result['currency']])) {
                $prices[$result['label']][$result['currency']] = 0.0;
            }

            $prices[$result['label']][$result['currency']] += (float) $result['value'] * $result['quantity'];
        }

        return $prices;
    }

    public function computeTotalPrices(array $collections, string $visibility): array
{
    $totalPrices = [];

    // Recorrer todas las colecciones
    foreach ($collections as $collection) {
        // Obtener precios de la colección actual
        $collectionPrices = $this->computePricesForCollection($collection, $visibility);

        // Sumar los precios de la colección al total
        foreach ($collectionPrices as $label => $currencies) {
            foreach ($currencies as $currency => $price) {
                // Acumular el precio para cada moneda
                if (!isset($totalPrices[$currency])) {
                    $totalPrices[$currency] = 0.0;
                }

                // Asegurarse de que se sumen los precios, no se sobrescriban
                $totalPrices[$currency] += $price;
            }
        }
    }

    return $totalPrices;
}


    public function findAllUniqueLabels()
    {
        return $this
            ->createQueryBuilder('datum')
            ->select('datum.label, datum.type')
            ->where("datum.type NOT IN ('image', 'sign', 'video', 'file', 'price')")
            ->addGroupBy('datum.label')
            ->addGroupBy('datum.type')
            ->addOrderBy('datum.label')
            ->getQuery()
            ->getResult()
        ;
    }
}
