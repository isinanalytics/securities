<?php

namespace SecuritiesService\Domain\Entity;

use JsonSerializable;
use SecuritiesService\Domain\ValueObject\Bucket;
use SecuritiesService\Domain\ValueObject\BucketUndated;
use SecuritiesService\Domain\ValueObject\UUID;
use SecuritiesService\Domain\ValueObject\ISIN;
use DateTime;

class Security extends Entity implements JsonSerializable
{
    private $name;
    private $isin;
    private $startDate;
    private $isInteresting;
    private $maturityDate;
    private $coupon;
    private $margin;
    private $moneyRaisedGBP;
    private $moneyRaisedUSD;
    private $moneyRaisedLocal;
    private $product;
    private $currency;
    private $company;
    private $source;

    public function __construct(
        UUID $id,
        ISIN $isin,
        string $name,
        DateTime $startDate,
        bool $isInteresting,
        float $moneyRaisedGBP = null,
        float $moneyRaisedUSD = null,
        float $moneyRaisedLocal = null,
        Product $product = null,
        Company $company = null,
        Currency $currency = null,
        DateTime $maturityDate = null,
        float $coupon = null,
        float $margin = null,
        string $source = null
    ) {
        parent::__construct($id);

        $this->name = $name;
        $this->isin = $isin;
        $this->startDate = $startDate;
        $this->isInteresting = $isInteresting;
        $this->currency = $currency;
        $this->moneyRaisedGBP = $moneyRaisedGBP;
        $this->moneyRaisedUSD = $moneyRaisedUSD;
        $this->moneyRaisedLocal = $moneyRaisedLocal;
        $this->product = $product;
        $this->company = $company;
        $this->maturityDate = $maturityDate;
        $this->coupon = $coupon;
        $this->margin = $margin;
        $this->source = $source;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getExchange(): string
    {
        // @todo - remove?
        return 'LSE';
    }

    public function getIsin(): string
    {
        return $this->isin;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function isInteresting(): bool
    {
        return $this->isInteresting;
    }

    public function getMaturityDate()
    {
        return $this->maturityDate;
    }

    public function getCoupon()
    {
        return $this->coupon;
    }

    public function getMargin()
    {
        return $this->margin;
    }

    public function getMoneyRaised()
    {
        return $this->moneyRaisedGBP;
    }

    public function getMoneyRaisedUSD()
    {
        return $this->moneyRaisedUSD;
    }

    public function getMoneyRaisedLocal()
    {
        return $this->moneyRaisedLocal;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTerm()
    {
        if ($this->maturityDate) {
            $interval = $this->startDate->diff($this->maturityDate);
            return $interval->y;
        }
        return null;
    }

    public function jsonSerialize($full = false)
    {
        $dateFormat = 'd/m/Y';

        $data = [
            'isin' => $this->getIsin(),
            'name' => $this->getName(),
            'startDate' => $this->getStartDate()->format($dateFormat),
            'maturityDate' => $this->getMaturityDate() ? $this->getMaturityDate()->format($dateFormat) : null,
            'coupon' => $this->getCoupon(),
            'margin' => $this->getMargin(),
            'amountRaised' => $this->getMoneyRaised(),
            'amountRaisedLocal' => $this->getMoneyRaisedLocal(),
            'currency' => $this->getCurrency() ? $this->getCurrency()->getCode() : null,
            'product' => $this->getProduct() ?? null,
            'issuer' => $this->getCompany() ?? null,
            'isInteresting' => $this->isInteresting(),
        ];

        ksort($data);

        if (!$full) {
            return (object) $data;
        }

        $data['source'] = $this->getSource();

        ksort($data);
        return $data;
    }
}
