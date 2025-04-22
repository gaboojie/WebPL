<?php

include "Manager.php";

// By Gabriel Jackson (tbp8gx) & Will Baker (ppt4pq)

class ProjectController {

    public $db;
    public $message = "";
    public $isErrorMessage = false;

    public function __construct($input) {
        session_start();
        $this->db = new Database();
        $this->input = $input;
    }

    public function run() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle POST requests
            $this->handle_post();
        } else {
            // Handle GET requests
            $this->handle_get();
        }
    }

    /**
     * Handles all GET requests
     */
    public function handle_get() {
        // Handle GET requests that include information in the URL
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

        // Handle simple GET requests
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

    /**
     * Handles all POST requests
     */
    public function handle_post() {
        // Handle POST requests
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
            case 'getMatchingProjects':
                getProjectsThatMatch($this);
                return;
            default:
                $this->showLandingPage();
        }
    }

    /**
     * Displays a project given a project_id
     */
    public function showProject($project_id) {
        // If a project does not exist, display the landing page instead
        $result = $this->db->getProjectsWithID($project_id);
        if (count($result) == 0) {
            $this->showLandingPage();
            return;
        }
        $graph = $result[0];

        // Determines if the currently-logged in user owns this project
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $graph['user_id']) {
            $owns = true;
        } else {
            $owns = false;
        }

        include("/opt/src/project/templates/visualizer.php");
    }

    /**
     * Displays all projects that match the search query in $_GET['search']
     */
    public function searchProjects() {
        $searchQuery = $_GET['search'];
        $searchTitle = "Graph Visualizations:";
        $searchType = "AllProjects";
        include("/opt/src/project/templates/search.php");
    }

    /**
     * Displays all projects that are associated with the currently-logged in user
     */
    public function showMyProjects() {
        $graphs = $this->db->getMyProjects($_SESSION['user_id']);
        $searchTitle = "Your Projects:";
        $searchType = "MyProjects";
        include("/opt/src/project/templates/search.php");
    }

    /**
     * Displays the sign-in page
     */
    public function showSignInPage() {
        $message = $this->message;
        $isErrorMessage = $this->isErrorMessage;
        include("/opt/src/project/templates/sign-in.php");
    }

    /**
     * Displays the landing page
     */
    public function showLandingPage() {
        include("/opt/src/project/templates/index.php");
    }
}