<?php

namespace Assessment\Availability\Todo;

use Assessment\Availability\EquimentAvailabilityHelper;
use Assessment\Manager\EquipmentTimelineManager;
use DateTime;

class EquimentAvailabilityHelperAssessment extends EquimentAvailabilityHelper
{
    /**
     * This function checks if a given quantity is available in the passed time frame
     * @param int      $equipment_id Id of the equipment item
     * @param int      $quantity How much should be available
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return bool True if available, false otherwise
     */
    public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end): bool
    {
        $plannedEquipmentItemsArray = $this->getEquipmentItemsPlannedInDateInterval($equipment_id, $start, $end);

        $timelineManager = new EquipmentTimelineManager();
        $timelineManager->loadTimelines($plannedEquipmentItemsArray, $start, $end);

        $timeline = $timelineManager->getTimeline($equipment_id);

        return $quantity <= max($timeline->getAvailabilities());
    }

    /**
     * Calculate all items that are short in the given period
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return array Key/valyue array with as indices the equipment id's and as values the shortages
     */
    public function getShortages(DateTime $start, DateTime $end): array
    {
        $plannedEquipmentItemsArray = $this->getAllEquipmentItemsPlannedInDateInterval($start, $end);

        $timelineManager = new EquipmentTimelineManager();
        $timelineManager->loadTimelines($plannedEquipmentItemsArray, $start, $end);

        $timelines = $timelineManager->getTimelines();
        $shortages = [];
        foreach ($timelines as $equipmentId => $timeline) {
            $equipmentShortages = $timeline->getShortages();

            if (empty($equipmentShortages)) {
                continue;
            }

            $shortages[$equipmentId] = min($equipmentShortages);
        }

        return $shortages;
    }
}
