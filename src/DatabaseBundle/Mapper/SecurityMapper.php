<?php

namespace DatabaseBundle\Mapper;

use AppBundle\Domain\Entity\Entity;
use DatabaseBundle\Entity\Entity as EntityOrm;
use AppBundle\Domain\Entity\Security;
use AppBundle\Domain\ValueObject\ISIN;
use AppBundle\Domain\ValueObject\ID;

class SecurityMapper extends Mapper
{
    public function getDomainModel(EntityOrm $item): Entity
    {
        $id = new ID($item->getId());
        $isin = new ISIN($item->getIsin());
        $security = new Security(
            $id,
            $item->getCreatedAt(),
            $item->getUpdatedAt(),
            $isin,
            $item->getName()
        );
        return $security;
    }
}
