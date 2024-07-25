<?php

namespace App\Service;

use App\Contract\TravelCostCalculatorInterface;
use App\DTO\TravelCostRequest;
use App\Contract\DiscountStrategyInterface;

class TravelCostCalculator implements TravelCostCalculatorInterface
{
    private array $discountStrategies;

    public function __construct(array $discountStrategies)
    {
        foreach ($discountStrategies as $strategy) {
            if (!$strategy instanceof DiscountStrategyInterface) {
                throw new \InvalidArgumentException('All discount strategies must implement DiscountStrategyInterface.');
            }
        }
        $this->discountStrategies = $discountStrategies;
    }

    public function calculate(TravelCostRequest $request): float
    {
        $baseCost = $request->getBaseCost();
        $discountedCost = $this->applyAgeDiscount($baseCost, $request->getBirthDate(), $request->getStartDate());

        $totalDiscount = 0;
        foreach ($this->discountStrategies as $strategy) {
            $discount = $strategy->apply($discountedCost, $request->getStartDate(), $request->getPaymentDate());
            $totalDiscount += $discount;
        }

        $discountedCost -= min($totalDiscount, 1500);

        return max($discountedCost, 0);
    }


    private function applyAgeDiscount(float $baseCost, \DateTime $birthDate, \DateTime $startDate): float
    {
        $age = $startDate->diff($birthDate)->y;
        $discount = 0;
        if ($age >= 3 && $age < 6) {
            $discount = $baseCost * 0.8;
        } elseif ($age >= 6 && $age < 12) {
            $discount = min($baseCost * 0.3, 4500);
        } elseif ($age < 18) {
            $discount = $baseCost * 0.1;
        }
        return max($baseCost - $discount, 0);
    }
}
