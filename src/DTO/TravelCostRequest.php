<?php

namespace App\DTO;

use Exception;
use Symfony\Component\Validator\Constraints as Assert;

class TravelCostRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\GreaterThan(value: 0)]
    private float $baseCost;

    #[Assert\NotBlank]
    #[Assert\Date]
    private string $startDate;

    #[Assert\NotBlank]
    #[Assert\Date]
    private string $birthDate;

    #[Assert\NotBlank]
    #[Assert\Date]
    private string $paymentDate;

    public function __construct(float $baseCost, string $startDate, string $birthDate, string $paymentDate)
    {
        $this->baseCost = $baseCost;
        $this->startDate = $startDate;
        $this->birthDate = $birthDate;
        $this->paymentDate = $paymentDate;
    }

    public function getBaseCost(): float
    {
        return $this->baseCost;
    }

    /**
     * @throws Exception
     */
    public function getStartDate(): \DateTime
    {
        return new \DateTime($this->startDate);
    }

    /**
     * @throws Exception
     */
    public function getBirthDate(): \DateTime
    {
        return new \DateTime($this->birthDate);
    }

    /**
     * @throws Exception
     */
    public function getPaymentDate(): \DateTime
    {
        return new \DateTime($this->paymentDate);
    }
}
