DROP TABLE IF EXISTS ProjectUsers;
DROP TABLE IF EXISTS ProjectGraphs;

CREATE TABLE IF NOT EXISTS ProjectUsers (
    user_id SERIAL PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS ProjectGraphs (
    project_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    graph_data JSON,
    graph_code TEXT,
    created DATE NOT NULL,
    graph_type TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES ProjectUsers(user_id)
);