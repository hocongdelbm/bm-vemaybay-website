<?php
namespace SuiteCRM\Modules\Administration\Search\ElasticSearch;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use BeanFactory;
use Configurator;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Exception;
use Scheduler;
use SchedulersJob;
use SugarJobQueue;
use SuiteCRM\Modules\Administration\Search\MVC\Controller as AbstractController;
use SuiteCRM\Search\ElasticSearch\ElasticSearchClientBuilder;
use SuiteCRM\Search\ElasticSearch\ElasticSearchIndexer;
use Throwable;

require_once __DIR__ . '/../../../Configurator/Configurator.php';
require_once __DIR__ . '/../../../SchedulersJobs/SchedulersJob.php';
require_once __DIR__ . '/../../../../include/SugarQueue/SugarJobQueue.php';

/**
 * Class Controller handles the actions for the Elasticsearch settings.
 */
class Controller extends AbstractController
{
    /**
     * ElasticSearchSettingsController constructor.
     */
    public function __construct()
    {
        parent::__construct(new View());
    }

    /**
     * Shows the view.
     */
    public function display(): void
    {
        $this->view->getTemplate()->assign('schedulers', $this->getSchedulers());
        parent::display();
    }

    /**
     * Saves the configuration getting data from POST.
     */
    public function doSaveConfig()
    {
        $enabled = filter_input(INPUT_POST, 'enabled', FILTER_VALIDATE_BOOLEAN);
        $host = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
        $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);

        $enabled = boolval(intval($enabled));

        $cfg = new Configurator();

        $cfg->config['search']['ElasticSearch']['enabled'] = $enabled;
        $cfg->config['search']['ElasticSearch']['host'] = $host;
        $cfg->config['search']['ElasticSearch']['user'] = $user;
        $cfg->config['search']['ElasticSearch']['pass'] = $pass;

        $cfg->saveConfig();

        if ($this->isAjax()) {
            $this->yieldJson(['status' => 'success']);
        }

        $this->redirect('index.php?module=Administration&action=index');
    }

    /**
     * Test the connection with the Elasticsearch and returns a json.
     */
    public function doTestConnection()
    {
        $input = INPUT_POST;

        $host = filter_input($input, 'host', FILTER_SANITIZE_STRING);
        $user = filter_input($input, 'user', FILTER_SANITIZE_STRING);
        $pass = filter_input($input, 'pass', FILTER_SANITIZE_STRING);

        try {
            $config = [
                ElasticSearchClientBuilder::sanitizeHost([
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass,
                ]),
            ];

            $client = ClientBuilder::create()->setHosts($config)->build();

            $indexer = new ElasticSearchIndexer($client);

            $return = ['status' => 'fail', 'request' => $config[0],];

            $info = $client->info();
            $time = $indexer->ping();

            $return['status'] = 'success';
            $return['ping'] = $time;
            $return['info'] = $info;
        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (BadRequest400Exception $exception) {
            $error = json_decode($exception->getMessage());
            $return['error'] = $error->error->reason;
            $return['errorDetails'] = $error;
        } catch (Exception $exception) {
            $return['error'] = $exception->getMessage();
            $return['errorType'] = get_class($exception);
        } catch (Throwable $throwable) {
            $return['error'] = $throwable->getMessage();
            $return['errorType'] = get_class($throwable);
        }

        $this->yieldJson($return);
    }

    /**
     * Schedules a full indexing.
     */
    public function doFullIndex()
    {
        $this->scheduleIndex(false);
    }

    /**
     * Schedules a partial indexing.
     */
    public function doPartialIndex()
    {
        $this->scheduleIndex(true);
    }

    /**
     * Returns all the Elasticsearch-related scheduler jobs.
     *
     * @return Scheduler[]|null
     */
    public function getSchedulers()
    {
        $where = "schedulers.job='function::runElasticSearchIndexerScheduler'";
        /** @var Scheduler[]|null $schedulers */
        $schedulers = BeanFactory::getBean('Schedulers')->get_full_list(null, $where);

        return $schedulers;
    }

    /**
     * Schedules an indexing job.
     *
     * @param bool $partial
     */
    protected function scheduleIndex($partial)
    {
        $job = new SchedulersJob();

        $job->name = 'Index requested by an administrator';
        $job->target = 'function::runElasticSearchIndexerScheduler';
        $job->data = json_encode(['partial' => $partial]);
        $job->assigned_user_id = 1;

        $queue = new SugarJobQueue();
        /** @noinspection PhpParamsInspection */
        $queue->submitJob($job);

        $this->yieldJson(['status' => 'success']);
    }
}
