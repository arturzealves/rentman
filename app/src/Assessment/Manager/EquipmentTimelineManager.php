<?php

namespace Assessment\Manager;

use Assessment\Model\EquipmentTimeline;
use DateTime;
use DateInterval;
use DatePeriod;

class EquipmentTimelineManager
{
    /**
     * Stores an array of EquipmentTimeline instances.
     * @var array
     */
    private $timelines;

    public function __construct()
    {
        $this->timelines = [];
    }

    /**
     * Adds a new EquipmentTimeline to the timelines array
     */
    public function add(int $equipmentId, int $stock, DateTime $start, DateTime $end): void
    {
        $this->timelines[$equipmentId] = new EquipmentTimeline(
            $equipmentId,
            $stock,
            $start,
            $end
        );
    }

    /**
     * Checks if the timelines array already contains a given equipment
     */
    public function contains(int $equipmentId): bool
    {
        return isset($this->timelines[$equipmentId]);
    }

    /**
     * Loads the timelines array with the given planning data for a given date interval
     */
    public function loadTimelines(array $planningData, DateTime $start, DateTime $end): void
    {
        foreach ($planningData as $plannedEquipmentArray) {
            $equipmentId = $plannedEquipmentArray['equipment'];

            if (!$this->contains($equipmentId)) {
                $this->add(
                    $equipmentId,
                    $plannedEquipmentArray['stock'],
                    $start,
                    $end
                );
            }

            $endDate = new DateTime($plannedEquipmentArray['end']);
            $endDate->modify('+1 day');

            $planningPeriod = new DatePeriod(
                new DateTime($plannedEquipmentArray['start']),
                DateInterval::createFromDateString('1 day'),
                $endDate
            );

            foreach ($planningPeriod as $date) {
                $this->timelines[$equipmentId]->addDateQuantity($date, $plannedEquipmentArray['quantity']);
            }
        }
    }

    /**
     * Returns all existing timelines
     */
    public function getTimelines(): array
    {
        return $this->timelines;
    }

    /**
     * Returns the EquipmentTimeline from the given equipment id or null if it does not exist
     */
    public function getTimeline($equipmentId): ?EquipmentTimeline
    {
        return $this->contains($equipmentId)
            ? $this->timelines[$equipmentId]
            : null;
    }
}
