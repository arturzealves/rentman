<?php

namespace Assessment\Availability;

use DateTime;
use PDO;

abstract class EquimentAvailabilityHelper
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * EquimentAvailabilityHelper constructor.
     * @param PDO $oDatabaseConnection
     */
    public function __construct(private PDO $oDatabaseConnection)
    {
        $this->oDatabaseConnection = $oDatabaseConnection;
    }

    /**
     * Get the already opened connection to the assessment database
     * @return PDO
     */
    final public function getDatabaseConnection(): PDO
    {
        return $this->oDatabaseConnection;
    }

    final public function getEquipmentItems(): array
    {
        $aRows = $this->oDatabaseConnection
            ->query('SELECT * FROM equipment')
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_column($aRows, null, 'id');
    }

    /**
     * Get a given equipment with existing planning for a given date interval
     * @param DateTime $start
     * @param DateTime $end
     */
    final public function getEquipmentItemsPlannedInDateInterval(
        int $equipmentId,
        DateTime $start,
        DateTime $end
    ): array {
        $sth = $this->oDatabaseConnection
            ->prepare(<<<EOF
                SELECT *
                FROM equipment e
                RIGHT JOIN planning p ON e.id = p.equipment
                WHERE
                    p.equipment = :equipmentId
                    AND (
                        (p.start >= :start AND p.end <= :end)
                        OR (p.start <= :start AND p.end >= :start)
                        OR (p.start <= :end AND p.end >= :end)
                    )
                ORDER BY e.id ASC, p.start ASC, p.end ASC
            EOF);

        $sth->execute([
            'equipmentId' => $equipmentId,
            'start' => $start->format(self::DATE_FORMAT),
            'end' => $end->format(self::DATE_FORMAT),
        ]);

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all equipment with existing planning for a given date interval
     * @param DateTime $start
     * @param DateTime $end
     */
    final public function getAllEquipmentItemsPlannedInDateInterval(DateTime $start, DateTime $end): array
    {
        $sth = $this->oDatabaseConnection
            ->prepare(<<<EOF
                SELECT *
                FROM equipment e
                RIGHT JOIN planning p ON e.id = p.equipment
                WHERE (p.start >= :start AND p.end <= :end)
                    OR (p.start <= :start AND p.end >= :start)
                    OR (p.start <= :end AND p.end >= :end)
                ORDER BY e.id ASC, p.start ASC, p.end ASC
            EOF);

        $sth->execute([
            'start' => $start->format(self::DATE_FORMAT),
            'end' => $end->format(self::DATE_FORMAT),
        ]);

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * This function checks if a given quantity is available in the passed time frame
     * @param int      $equipment_id Id of the equipment item
     * @param int      $quantity How much should be available
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return bool True if available, false otherwise
     */
    abstract public function isAvailable(
        int $equipment_id,
        int $quantity,
        DateTime $start,
        DateTime $end
    ): bool;

    /**
     * Calculate all items that are short in the given period
     * @param DateTime $start Start of time window
     * @param DateTime $end End of time window
     * @return array Key/valyue array with as indices the equipment id's and as values the shortages
     */
    abstract public function getShortages(DateTime $start, DateTime $end) : array;
}
