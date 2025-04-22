<?php

// By: Gabriel Jackson (tbp8gx)

/**
 * Handles log-in logic
 */
function login($controller) {
    $db = $controller->db;

    // Determine if an account exists
    if ($db->doesUserExist($_POST["username"])) {
        // User does exist
        if ($db->doesPasswordMatch($_POST["username"], $_POST["password"])) {
            // User exists and password matches
            $_SESSION["username"] = (string) $_POST["username"];
            $_SESSION["password"] = (string) $_POST["password"];
            $_SESSION["user_id"] = $db->getUser($_POST["username"])['user_id'];
            $controller->showMyProjects();
        } else {
            // User exists but password does not match
            $controller->message = "Error: Incorrect password used.";
            $controller->isErrorMessage = true;
            $controller->showSignInPage();
        }
    } else {
        // User does not exist

        // Validate new username to contain only alphanumeric characters
        $regex_pattern = "/^[a-zA-Z0-9]+$/";
        if (preg_match($regex_pattern, $_POST["username"])) {
            // Valid username
            $_SESSION["username"] = (string) $_POST["username"];
            $_SESSION["password"] = (string) $_POST["password"];
            $hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $_SESSION["user_id"] = $db->createUser($_POST["username"], $hashedPassword)['user_id'];
            $controller->showMyProjects();
        } else {
            // Invalid username
            $controller->message = "Error: Your username must be alphanumeric!";
            $controller->isErrorMessage = true;
            $controller->showSignInPage();
        }
    }
}

/**
 * Handles logging-out logic
 */
function logout($controller) {
    session_unset();
    session_destroy();
    $controller->showLandingPage();
}

/**
 * Handles the logic that saves the info of a project (not code)
 */
function saveProject($controller) {
    $db = $controller->db;
    $title = $_POST["title"];
    $description = $_POST["description"];
    $graphType = $_POST["type"];
    $project_id = $_POST["project_id"];

    $db->updateProjectInfo($project_id, $title, $description, $graphType);
    $controller->showProject($project_id);
}

/**
 * Handles the logic that saves the code of a project
 */
function saveProjectCode($controller) {
    $db = $controller->db;
    $db->updateProjectCode($_POST["project_id"], $_POST["editorContent"]);
    $controller->showProject($_POST["project_id"]);
}

/**
 * Handles delete project logic
 */
function deleteProject($controller) {
    $db = $controller->db;
    $db->deleteProject($_GET["deleteProject"]);
    $controller->showMyProjects();
}

/**
 * Creates a default project
 */
function createProject($controller) {
    $db = $controller->db;
    $title = "New graph";
    $description = "To update the information for your project, use the setting icon above.";
    $graphType = "None";
    $owner = $_SESSION["user_id"];
    $project_id = $db->createProject($owner, $title, $description, "{}", "", $graphType);
    $controller->showProject($project_id);
}

function getProjectsThatMatch($controller) {
    $db = $controller->db;

    // If bad request, send 404
    if (!isset($_POST["isOwner"]) || !isset($_POST["searchQuery"]) || !isset($_POST["maxProjects"])) {
        http_response_code(400);
        return;
    }

    // If not logged in pass bad log in user id
    if (!isset($_SESSION["user_id"])) {
        $user_id = -1;
    } else {
        $user_id = $_SESSION["user_id"];
    }

    // Set content type to JSON
    header('Content-Type: application/json');

    $isOwner = $_POST["isOwner"] === 'true';
    $searchQuery = $_POST["searchQuery"];
    $maxProjects = $_POST["maxProjects"];

    // Add each project's data to the json
    $projects = $db->getProjectsThatMatch($isOwner, $user_id, $searchQuery, $maxProjects);
    $projectsData = [];
    foreach ($projects as $project) {
        $projectData = [
            "project_id" => $project['project_id'],
            "title" => $project['title'],
            "description" => $project['description'],
            "type" => $project['graph_type'],
            "owner_id" => $project['user_id'],
            "owner_username" => $project['username'],
            "graph_data" => $project['graph_data'],
            "graph_code" => $project['graph_code'],
            "created_date" =>  $project['created']
        ];
        $projectsData[] = $projectData;
    }

    echo json_encode($projectsData);
}

/**
 * Returns a project info as JSON
 */
function getProjectInfo($controller) {
    $db = $controller->db;

    // Set content type to JSON
    header('Content-Type: application/json');

    $projects = $db->getProjectsWithID($_GET["getProjectJSON"]);
    if (count($projects) == 0) {
        // If project does not exist, return error message
        $data = [
            "status"  => "Error",
            "message" => "No projects found with project_id: "  . $_GET["getProjectJSON"]
        ];
    } else {
        // If project exists, return project information
        $project = $projects[0];
        $data = [
            "status" => "Success",
            "message" => "Project found.",
            "project_id" => $project['project_id'],
            "title" => $project['title'],
            "description" => $project['description'],
            "type" => $project['graph_type'],
            "owner_id" => $project['user_id'],
            "owner_username" => $project['username'],
            "graph_data" => $project['graph_data'],
            "graph_code" => $project['graph_code'],
            "created_date" =>  $project['created']
        ];
    }

    // Convert the data to JSON format
    echo json_encode($data);
}