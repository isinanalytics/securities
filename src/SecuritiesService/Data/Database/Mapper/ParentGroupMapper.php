<?php

namespace SecuritiesService\Data\Database\Mapper;

use SecuritiesService\Domain\Entity\Entity;
use SecuritiesService\Domain\Entity\ParentGroup;
use SecuritiesService\Domain\ValueObject\ID;

class ParentGroupMapper extends Mapper
{
    public function getDomainModel(array $item): Entity
    {
        $id = new ID($item['id']);
        $sector = null;

        if (isset($item['sector'])) {
            $sector = $this->mapperFactory->createSector()->getDomainModel($item['sector']);
        }

        $currency = new ParentGroup(
            $id,
            $item['name'],
            $sector
        );
        return $currency;
    }
}