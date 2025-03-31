<?php

include_once "Manager.php";

class ProjectController {

    public $db;
    public $message = "";
    public $isErrorMessage = false;

    /**
     * Constructor
     */
    public function __construct($input) {
        session_start();
        $this->db = new Database();
        $this->input = $input;
    }

    public function run() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_post();
        } else {
            // Implicitly must be GET
            $this->handle_get();
        }
    }

    public function handle_get() {
        // Handle GET requests that include
        if (isset($_GET['search'])) {
            $this->searchProjects();
            return;
        } else if (isset($_GET['project_id'])) {
            $this->showProject($_GET['project_id']);
            return;
        } else if (isset($_GET['deleteProject'])) {
            deleteProject($this);
            return;
        } else if (isset($_GET['getProjectJSON'])) {
            getProjectInfo($this);
            return;
        }

        $command = 'landing';
        if (isset($_GET['command'])) {
            $command = $_GET['command'];
        }

        switch ($command) {
            case 'landing':
                $this->showLandingPage();
                return;
            case 'signinpage':
                $this->showSignInPage();
                return;
            case 'myprojects':
                $this->showMyProjects();
                return;
            case 'createproject':
                createProject($this);
                return;
            case 'logout':
                logout($this);
                return;
            default:
                $this->showLandingPage();
        }
    }

    public function handle_post() {
        // Default to landing page
        $command = 'landing';

        if (isset($_GET['command'])) {
            $command = $_GET['command'];
        }

        switch ($command) {
            case 'sign-in':
                login($this);
                return;
            case 'saveProject':
                saveProject($this);
                return;
            case 'saveProjectCode':
                saveProjectCode($this);
                return;
            default:
                $this->showLandingPage();
        }
    }

    public function showProject($project_id) {
        $graph = $this->db->getProjectByID($project_id);
        include("/opt/src/project/templates/visualizer.php");
    }

    public function searchProjects() {
        $searchQuery = $_GET['search'];
        $graphs = $this->db->getProjectsBySearchData($searchQuery);
        $searchTitle = "Graph Visualizations:";
        $searchType = "AllProjects";
        include("/opt/src/project/templates/search.php");
    }

    public function showMyProjects() {
        $graphs = $this->db->getMyProjects($_SESSION['user_id']);
        $searchTitle = "Your Projects:";
        $searchType = "MyProjects";
        include("/opt/src/project/templates/search.php");
    }

    public function showSignInPage() {
        $message = $this->message;
        $isErrorMessage = $this->isErrorMessage;
        include("/opt/src/project/templates/sign-in.php");
    }

    public function showLandingPage() {
        include("/opt/src/project/templates/index.php");
    }

}