<?php
/**
 * Database Class
 *
 * Contains connection information to query PostgresSQL.
 */


class Database {
    private $dbConnector;

    /**
     * Constructor
     *
     * Connects to PostgresSQL
     */
    public function __construct() {
        $host = Config::$db["host"];
        $user = Config::$db["user"];
        $database = Config::$db["database"];
        $password = Config::$db["pass"];
        $port = Config::$db["port"];

        $this->dbConnector = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");
    }

    /**
     * Query
     *
     * Makes a query to postgres and returns an array of the results.
     * The query must include placeholders for each of the additional
     * parameters provided.
     */
    public function query($query, ...$params) {
        $res = pg_query_params($this->dbConnector, $query, $params);

        if ($res === false) {
            echo pg_last_error($this->dbConnector);
            return false;
        }

        return pg_fetch_all($res);
    }

    // User based queries

    public function createUser($username, $passwordHash) {
        $this->query("INSERT INTO ProjectUsers (username, password_hash) VALUES ($1, $2);",  $username, $passwordHash);
        return $this->getUser($username);
    }

    public function doesUserExist($username) {
        $result = $this->query("SELECT COUNT(*) AS result FROM ProjectUsers WHERE username = $1", $username);
        return $result[0]['result'];
    }

    public function getUser($username) {
        $result = $this->query("SELECT * FROM ProjectUsers WHERE username = $1", $username);
        return $result[0];
    }

    public function doesPasswordMatch($username, $password) {
        $result = $this->query("SELECT * FROM ProjectUsers WHERE username = $1", $username);
        $hashed_password = $result[0]["password_hash"];
        return password_verify($password, $hashed_password);
    }

    // Project based queries

    public function createProject($user_id, $title, $description, $graph_data, $graph_code, $graph_type) {
        return $this->query("INSERT INTO ProjectGraphs (user_id, title, description, graph_data, graph_code, created, graph_type) VALUES ($1, $2, $3, $4, $5, CURRENT_DATE, $6) RETURNING project_id;",
            $user_id, $title, $description, $graph_data, $graph_code, $graph_type)[0]["project_id"];
    }

    public function getMyProjects($user_id) {
       return $this->query("SELECT * FROM ProjectGraphs NATURAL JOIN ProjectUsers WHERE user_id = $1 ;", $user_id);
    }

    public function getProjectsWithID($project_id) {
        return $this->query("SELECT * FROM ProjectGraphs NATURAL JOIN ProjectUsers WHERE project_id = $1", $project_id);
    }

    public function getProjectByID($project_id) {
        return $this->query("SELECT * FROM ProjectGraphs NATURAL JOIN ProjectUsers WHERE project_id = $1", $project_id)[0];
    }

    public function getProjectsBySearchData($search_data) {
        $search_data = '%' . $search_data . '%';
        return $this->query("SELECT * FROM ProjectGraphs NATURAL JOIN ProjectUsers WHERE title LIKE $1 OR graph_type LIKE $1", $search_data);
    }

    public function updateProjectInfo($project_id, $title, $description, $graph_type) {
        $this->query("UPDATE ProjectGraphs SET title = $1, description = $2, graph_type = $3 WHERE project_id = $4", $title, $description, $graph_type, $project_id);
    }

    public function deleteProject($project_id) {
        $this->query("DELETE FROM ProjectGraphs WHERE project_id = $1", $project_id);
    }

    public function updateProjectCode($project_id, $graph_code) {
        $this->query("UPDATE ProjectGraphs SET graph_code = $1 WHERE project_id = $2", $graph_code, $project_id);
    }

}
