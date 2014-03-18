<?php
namespace Meetingroom\Entity\Event\Lookupper;

use \Meetingroom\Entity\Event\Lookupper\Criteria\RoomCriteriaInterface;
use \Meetingroom\Entity\Event\Lookupper\Criteria\PeriodCriteriaInterface;


class EventLookupperModel extends \Meetingroom\Model\AbstractModel
{

    /**
     * @param RoomCriteriaInterface $roomCriteria
     * @param PeriodCriteriaInterface $periodCriteria
     * @param array $fields
     * @return array array of \Meetingroom\Entity\Event\Event
     */
    public function getEvents(
        RoomCriteriaInterface $roomCriteria,
        PeriodCriteriaInterface $periodCriteria,
        array $fields = []
    ) {
        $sql = $this->buildQuery($roomCriteria, $periodCriteria, $fields);

        $result = $this->execute($sql);

        $list = [];
        foreach ($result as $id => $data) {

            $list[$id] = (new \Meetingroom\Entity\Event\EventEntity())->bind($data);
        }

        return $list;
    }

    public function checkConflict($event)
    {
        //var_dump('<pre>',$event);
        var_dump('<pre>', $event->date_start, $event->room_id);
        die();

        $weekday = strtolower(date("D", $event->date_start));
        $eventBuilder = new \Meetingroom\Entity\Event\Lookupper\Builder\CheckConflictBuilder();

        $sql = $eventBuilder->build($event->room_id, $weekday);

        var_dump($sql);

        $result = $this->execute();
        return !empty($result);
    }

    /**
     * @param RoomCriteriaInterface $roomCriteria
     * @param PeriodCriteriaInterface $periodCriteria
     * @param array $fields
     * @return string
     */
    protected function buildQuery(
        RoomCriteriaInterface $roomCriteria,
        PeriodCriteriaInterface $periodCriteria,
        array $fields = []
    ) {
        $eventBuilder = new \Meetingroom\Entity\Event\Lookupper\Builder\EventBuilder();

        return $eventBuilder->build($roomCriteria, $periodCriteria, $fields);

    }

    /**
     * @param string $sql
     * @return array
     */
    protected function execute($sql)
    {
        $result = $this->db->query($sql);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        return $result->fetchAll();
    }

} 