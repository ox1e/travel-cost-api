<?php

namespace App\Service\DiscountStrategy;

use App\Contract\DiscountStrategyInterface;

class EarlyBookingDiscountStrategy implements DiscountStrategyInterface
{
    private const array DISCOUNT_PERIODS = [
        [
            'start' => '04-01',
            'end' => '09-30',
            'paymentPeriods' => [
                ['end' => '11-30', 'discount' => 0.07],
                ['end' => '12-31', 'discount' => 0.05],
                ['end' => '01-31', 'discount' => 0.03],
            ]
        ],
        [
            'start' => '10-01',
            'end' => '01-14',
            'paymentPeriods' => [
                ['end' => '03-31', 'discount' => 0.07],
                ['end' => '04-30', 'discount' => 0.05],
                ['end' => '05-31', 'discount' => 0.03],
            ]
        ],
        [
            'start' => '01-15',
            'end' => '12-31',
            'paymentPeriods' => [
                ['end' => '08-31', 'discount' => 0.07],
                ['end' => '09-30', 'discount' => 0.05],
                ['end' => '10-31', 'discount' => 0.03],
            ]
        ],
    ];

    public function apply(float $cost, \DateTime $startDate, \DateTime $paymentDate): float
    {
        $discount = $this->calculateDiscount($startDate, $paymentDate);
        return min($cost * $discount, 1500);
    }

    private function calculateDiscount(\DateTime $startDate, \DateTime $paymentDate): float
    {
        $startYear = (int)$startDate->format('Y');

        foreach (self::DISCOUNT_PERIODS as $period) {
            $periodStart = \DateTime::createFromFormat('Y-m-d', "$startYear-{$period['start']}");
            $periodEnd = \DateTime::createFromFormat('Y-m-d', "$startYear-{$period['end']}");

            if ($periodEnd < $periodStart) {
                $periodEnd->modify('+1 year');
            }

            if ($startDate >= $periodStart && $startDate <= $periodEnd) {
                return $this->getDiscountForPeriod($period['paymentPeriods'], $startDate, $paymentDate);
            }
        }

        return 0;
    }

    private function getDiscountForPeriod(array $paymentPeriods, \DateTime $startDate, \DateTime $paymentDate): float
    {
        $referenceYear = (int)$startDate->format('Y') - 1;

        foreach ($paymentPeriods as $period) {
            $endDate = \DateTime::createFromFormat('Y-m-d', "$referenceYear-{$period['end']}");
            if ($period['end'] === '01-31') {
                $endDate->modify('+1 year');
            }
            if ($paymentDate <= $endDate) {
                return $period['discount'];
            }
        }

        return 0;
    }
}
