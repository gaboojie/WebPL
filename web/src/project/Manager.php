<?php

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

function logout($controller) {
    session_unset();
    session_destroy();
    $controller->showLandingPage();
}

function saveProject($controller) {
    $db = $controller->db;
    $title = $_POST["title"];
    $description = $_POST["description"];
    $graphType = $_POST["type"];
    $project_id = $_POST["project_id"];

    $db->updateProjectInfo($project_id, $title, $description, $graphType);
    $controller->showProject($project_id);
}

function saveProjectCode($controller) {
    $db = $controller->db;
    $db->updateProjectCode($_POST["project_id"], $_POST["editorContent"]);
    $controller->showProject($_POST["project_id"]);
}

function deleteProject($controller) {
    $db = $controller->db;
    $db->deleteProject($_GET["deleteProject"]);
    $controller->showMyProjects();
}

function createProject($controller) {
    $db = $controller->db;
    $title = "Example title";
    $description = "Example description";
    $graphType = "Example type";
    $owner = $_SESSION["user_id"];
    $project_id = $db->createProject($owner, $title, $description, "{}", "", $graphType);
    $controller->showProject($project_id);
}

function getProjectInfo($controller) {
    $db = $controller->db;
    header('Content-Type: application/json');

    $projects = $db->getProjectsWithID($_GET["getProjectJSON"]);
    if (count($projects) == 0) {
        $data = [
            "status"  => "Error",
            "message" => "No projects found with project_id: "  . $_GET["getProjectJSON"]
        ];
    } else {
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