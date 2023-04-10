<?php

namespace Assessment\Model;

use DateTime;
use DateInterval;
use DatePeriod;

class EquipmentTimeline
{
    /**
     * Stores the daily planned quantities for an equipment
     * @var array
     */
    private $planning;

    /**
     * Stores the daily available stock for an equipment
     * @var array
     */
    private $available;

    public function __construct(
        private int $equipmentId,
        private int $stock,
        private DateTime $start,
        private DateTime $end
    ) {
        // Creates a daily DatePeriod for the given dates interval
        $end->modify('+1 day');
        $period = new DatePeriod($start, DateInterval::createFromDateString('1 day'), $end);
        $end->modify('-1 day');

        // Initialize arrays with the correct value for each possible day in the given interval
        foreach ($period as $day) {
            $this->planning[$day->format('Y-m-d')] = 0;
            $this->available[$day->format('Y-m-d')] = $stock;
        }
    }

    /**
     * For a given date, sums the given quantity and subtracts the available equipment stock
     * @param DateTime $date The given date
     * @param int $quantity The quantity
     */
    public function addDateQuantity(DateTime $date, int $quantity): void
    {
        if ($date < $this->start || $date > $this->end) {
            return;
        }

        $this->planning[$date->format('Y-m-d')] += $quantity;
        $this->available[$date->format('Y-m-d')] -= $quantity;
    }

    /**
     * Gets all daily shortages for this equipment
     * @return array With dates as keys and the shortage amount as value
     */
    public function getShortages(): array
    {
        return array_filter($this->available, function ($value) {
            return $value < 0;
        });
    }

    /**
     * Returns the daily availabilities for this equipment
     * @return array With dates as keys and the available amount as value
     */
    public function getAvailabilities(): array
    {
        return $this->available;
    }
}
