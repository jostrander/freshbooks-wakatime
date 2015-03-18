<?php
/**
 * Created by PhpStorm.
 * User: jesse
 * Date: 18/03/15
 * Time: 1:41 AM
 */

namespace App\Boot;

use Dotenv\Dotenv;
use App\Config\Config;
use Freshbooks\FreshBooksApi;
use GuzzleHttp\Client;
use App\WakaTime;

class Bootstrap {
    private $freshbooks_projects;
    private $wakatime_projects;

    public static function start() {
        $bootstrap = new self();
        $bootstrap->displayFreshbooksProjects();
        $bootstrap->displayWakaTimeProjects();
        $bootstrap->transferProjects();
    }
    public function displayFreshbooksProjects() {
        $fb = new FreshBooksApi("rockethouse", Config::get_freshbooks_key());
        $fb->setMethod("project.list");
        $fb->request();
        if ($fb->success()) {
            $response = $fb->getResponse();
            echo "------Freshbooks------\n";
            foreach ($response['projects']['project'] as $project) {
                $this->freshbooks_projects[$project['name']] = $project['project_id'];
                echo "Name: " . $project['name'] . "\n";
                echo "Project Id: " . $project['project_id'] . "\n";
            }
        } else {
            echo "Failed to return Freshbooks Projects";
        }
    }

    /**
     *
     */
    private function displayWakaTimeProjects()
    {
        $wakatime = new WakaTime(new Client());
        $wakatime->setApiKey(Config::get_wakatime_key());
        echo "\n\n-------WAKATIME-------\n";
        foreach ($wakatime->dailySummary(strtotime("today"), strtotime("today"))['data'][0]['projects'] as $project) {
            $this->wakatime_projects[$project['name']] = round($project['total_seconds'] / 3600, 2);
            echo "Project: {$project['name']} \n";
            echo "Time: {$project['text']}\n";
        }
    }

    private function transferProjects() {
        echo "\n\n-----Importing!------\n\n";
        if (!empty($this->freshbooks_projects) && !empty($this->wakatime_projects)) {
            foreach ($this->freshbooks_projects as $key => $value) {
                if (isset($this->wakatime_projects[$key])) {
                    echo "Matched Project: {$key}\n";
                    echo "Hours: {$this->wakatime_projects[$key]}\n";
                    $this->createTimeEntry($value, $this->wakatime_projects[$key]);
                } else {
                    echo "No WakaTime Entries Found for FB Project: {$key}\n";
                }
            }
        } else {
            echo "Either no projects found in Freshbooks or Wakatime...\n";
        }
    }
    private function createTimeEntry($project_id, $hours, $task_id = null) {
        if (!$task_id) $task_id = Config::get_freshbooks_task();
        $fb = new FreshBooksApi("rockethouse", Config::get_freshbooks_key());
        $fb->setMethod("time_entry.create");
        $fb->post([
            'time_entry' => [
                'project_id' => $project_id,
                'task_id' => $task_id,
                'notes' => 'Imported from WakaTime',
                'hours' => $hours
            ]
        ]);
        $fb->request();
        if ($fb->success()) {
            echo "------Imported Into Freshbooks------\n";
        } else {
            echo "Failed to Import";
            echo $fb->getError();
        }
    }
}