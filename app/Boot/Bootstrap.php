<?php
/**
 * Created by PhpStorm.
 * User: jesse
 * Date: 18/03/15
 * Time: 1:41 AM
 */

namespace App\Boot;
use App\Config\Config;
use Freshbooks\FreshBooksApi;
use GuzzleHttp\Client;
use App\WakaTime;
use \Mandrill;


class Bootstrap {
    private $freshbooks_projects;
    private $wakatime_projects;

    private $mandrill;

    /**
     * @var \DateTime $date
     */
    private $date;
    private $logLines = [];

    public static function start(\DateTime $date = null)
    {
        $bootstrap = new self();
        $bootstrap->setDate($date);
        $bootstrap->displayFreshbooksProjects();
        $bootstrap->displayWakaTimeProjects();
        $bootstrap->transferProjects();
    }
    public function __construct() {
        $this->mandrill = new \Mandrill(Config::get("MANDRILL.API_KEY"));
    }
    public function setDate(\DateTime $date) {
        $this->date = $date;
    }
    public function displayFreshbooksProjects() {
        $fb = new FreshBooksApi(Config::get("FRESHBOOKS_SUB_DOMAIN"), Config::get("FRESHBOOKS_API_KEY"));
        $fb->setMethod("project.list");
        $fb->request();
        if ($fb->success()) {
            $response = $fb->getResponse();
            $this->logLines[] =  "------Freshbooks------";
            foreach ($response['projects']['project'] as $project) {
                $this->freshbooks_projects[$project['name']] = $project['project_id'];
                $this->logLines[] =  "Name: " . $project['name'];
                $this->logLines[] =  "Project Id: " . $project['project_id'];
            }
        } else {
            $this->logLines[] =  "Failed to return Freshbooks Projects";
        }
    }

    /**
     *
     */
    private function displayWakaTimeProjects()
    {
        $wakatime = new WakaTime(new Client());
        $wakatime->setApiKey(Config::get("WAKATIME_API_KEY"));
        $this->logLines[] =  "\n\n-------WAKATIME-------";
        foreach ($wakatime->dailySummary($this->date->format("m/d/Y"), $this->date->format("m/d/Y"))['data'][0]['projects'] as $project) {
            $this->wakatime_projects[$project['name']] = round($project['total_seconds'] / 3600, 2);
            $this->logLines[] =  "Project: {$project['name']} ";
            $this->logLines[] =  "Time: {$project['text']}";
        }
    }

    private function transferProjects() {
        $this->logLines[] =  "\n\n-----Importing!------\n\n";
        if (!empty($this->freshbooks_projects) && !empty($this->wakatime_projects)) {
            foreach ($this->freshbooks_projects as $key => $value) {
                if (isset($this->wakatime_projects[$key])) {
                    $this->logLines[] =  "Matched Project: {$key}";
                    $this->logLines[] =  "Hours: {$this->wakatime_projects[$key]}";
                    $this->createTimeEntry($value, $this->wakatime_projects[$key]);
                } else {
                    $this->logLines[] =  "No WakaTime Entries Found for FB Project: {$key}";
                }
            }
        } else {
            $this->logLines[] = "Either no projects found in FreshBooks or WakaTime...";
        }
    }
    private function createTimeEntry($project_id, $hours, $task_id = null) {
        $date = new \DateTime();
        if (!$task_id) $task_id = Config::get("FRESHBOOKS_TASK_ID");
        $fb = new FreshBooksApi(Config::get("FRESHBOOKS_SUB_DOMAIN"), Config::get("FRESHBOOKS_API_KEY"));
        $fb->setMethod("time_entry.create");
        $fb->post([
            'time_entry' => [
                'project_id' => $project_id,
                'task_id' => $task_id,
                'notes' => 'Imported from WakaTime on ' . $date->format('d/m/y : h:iA') . ' from ' . $this->date->format("d/m/y"),
                'hours' => $hours,
		        'date' => $this->date->format('Y-m-d')
            ]
        ]);
        $fb->request();
        if ($fb->success()) {
            $this->logLines[] = "------Imported Into Freshbooks------";
        } else {
            $this->logLines[] = "Failed to Import";
            $this->logLines[] = $fb->getError();
        }
        $message = [
            'text' => implode("\n", $this->logLines),
            'subject' => 'WakaTime Export Log',
            'from_email' => Config::get('MANDRILL.FROM_EMAIL'),
            'from_name' => Config::get('MANDRILL.FROM_NAME'),
            'to' => [
                [
                    'email' => Config::get('MANDRILL.TO_EMAIL'),
                    'type' => 'to'
                ]
            ],
        ];
        try {
            $this->mandrill->messages->send($message);
        } catch (\Mandrill_Error $e) {
            echo 'A mandrill error occurred: '. get_class($e) . ' - ' . $e->getMessage();

        }

    }
}
