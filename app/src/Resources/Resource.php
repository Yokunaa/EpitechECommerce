<?php

namespace App\Resources;

use Doctrine\ORM\EntityManagerInterface;

class Resource
{
    protected $hidden = [];

    public function __construct(private EntityManagerInterface $em)
    {
        //
    }

    public function resource($entity)
    {
        $cols = $this->em->getClassMetadata(get_class($entity))->getFieldNames();
        $values = [];

        if (isset($this->only)) {
            foreach ($cols as $col) {
                if (!in_array($col, $this->only)) {
                    continue;
                }
                $getter = 'get' . ucfirst($col);
                $values[$col] = $entity->$getter();
            }
        } else {
            foreach ($cols as $col) {
                if (in_array($col, $this->hidden)) {
                    continue;
                }
                $getter = 'get' . ucfirst($col);
                $values[$col] = $entity->$getter();
            }
        }

        return $values;
    }

    public function resourceCollection($collection)
    {
        $resourceCollection = [];

        foreach ($collection as $entity) {
            $resourceCollection[] = $this->resource($entity);
        }

        return $resourceCollection;
    }
}
