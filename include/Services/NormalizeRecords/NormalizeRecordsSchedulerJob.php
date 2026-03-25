<?php



if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once __DIR__ . '/../../SugarQueue/SugarJobQueue.php';
require_once __DIR__ . '/../../../modules/SchedulersJobs/SchedulersJob.php';
require_once __DIR__ . '/NormalizeRecords.php';


class NormalizeRecordsSchedulerJob extends SchedulersJob
{

    public $name = 'Repair field encoding';
    public $target = 'class::NormalizeRecords';

    /**
     * @param array $data
     */
    public static function scheduleJob(array $data): void
    {
        NormalizeRecords::getRepairStatus();

        $job = new self();

        $job->name = 'repair utf encoding';
        $job->data = json_encode(array_merge(['partial' => true], $data), JSON_THROW_ON_ERROR);
        $job->assigned_user_id = 1;

        $queue = new SugarJobQueue();
        /** @noinspection PhpParamsInspection */
        $queue->submitJob($job);

        NormalizeRecords::setRepairStatus(NormalizeRecords::REPAIR_STATUS_IN_PROGRESS);
    }

    /**
     * Get Scheduler job bean
     * @return SugarBean
     */
    public static function getJob(): SugarBean
    {
        return BeanFactory::getBean('SchedulersJobs', 'repair-utf-encoding');
    }
}
