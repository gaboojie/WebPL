<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Gabriel Jackson (tbp8gx) and Will Baker (ppt4pq)">
    <title>Code Visualizer</title>

    <!-- Include bootstrap and main.css dependencies-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="res/styles/main.css">

    <!--Include support for ACE to render text as code -->
    <!-- Source: https://ace.c9.io/ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>

    <!-- Include support for Vis.js network graph tool-->
    <!-- Source: https://visjs.org/ -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

    <!-- Include support for jQuery -->
    <!-- Source: https://releases.jquery.com/ -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Use 'main.js' file to create interactivity between DOM, ACE, and Vis.js -->
    <script src="res/scripts/worker.js" type="text/javascript"></script>
    <script src="res/scripts/main.js" type="text/javascript"></script>

    <!--Include support for ACE to render text as code -->
    <!--Source: https://ace.c9.io/ -->
    <script src="https://cdn.jsdelivr.net/npm/ace-builds@1.40.0/src-noconflict/snippets/python.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/ace-builds@1.40.0/css/ace.min.css" rel="stylesheet">
</head>
<body>

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<div class="container" style="min-height: 80vh; margin-top: 5vh;">
    <!--Show project information-->
    <div class="fluid-container">
        <div class="card bg-light text-dark mb-3 border-dark" style="width: 100%">
            <div class="card-body" style="width: 100%">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="fw-bold" style="color: rgb(207, 168, 115);">
                        Project: <?php echo $graph['title']; ?>
                    </h1>
                    <!-- If owner, show settings button-->
                    <?php
                    if ($owns) {
                        echo <<<HTML
                    <button type="button" class="btn btn-light btn-outline-dark" title="Settings" style="width: 80px; height: 40px;" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear"></i>
                    </button>
                HTML;
                    }
                    ?>
                </div>

                <span class="graph-text-container graph-title-text">
                    Type: <?php echo $graph['graph_type']; ?>
                </span>
                <span class="graph-text-container">
                    <span class="graph-owner-text">
                        By: <?php echo $graph['username']; ?>
                    </span>
                </span>
                <span class="graph-text-container">
                    <span class="graph-info-text">
                        <?php echo $graph['description']; ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- Display development environment -->
    <div class="card bg-light text-dark p-5 border-dark">
        <div class="d-flex align-items-center text-center">
            <div id="errorBanner" class="alert alert-danger d-flex d-none justify-content-between align-items-center" role="alert" style="min-width: 100%">
                <div id="errorText">
                    Error:
                </div>
                <button id="errorButton" type="button" class="close" data-dismiss="alert" aria-label="Close" style="border: none; outline: none; background-color: transparent;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <!-- Task bar -->
        <div class="d-flex justify-content-between align-items-center">
            <!-- Show save button if currently-logged in user is owner-->
            <div>
                <button id="add" class="btn btn-outline-dark">Add</button>
                <button id="remove" class="btn btn-outline-dark">Remove</button>
                <button id="physics" class="btn btn-outline-dark">Physics - Off</button>
                <button id="directed" class="btn btn-outline-dark">Graph - Undirected</button>
                <button id="smoothEdge" class="btn btn-outline-dark">Edges - Straight</button>
            </div>

            <div class="d-flex flex-row align-items-center">
                <div id="statusText" class="m-3 text-danger fw-bold d-none">
                    Running...
                </div>
                <button id="runButton" type="button" class="btn m-1 btn-light btn-outline-dark" title="Run">
                    <i id="runIcon" class="bi bi-play-fill"></i>
                </button>
                <?php
                if ($owns) {
                    echo <<<HTML
                        <form id="submitForm" action="?command=saveProjectCode" method="POST">
                            <input type="hidden" name="project_id" id="project_id" value="
                    HTML;
                    echo $graph['project_id'];
                    echo <<<HTML
                         "><input type="hidden" name="editorContent" id="editorContent">
                            <button type="submit" class="btn btn-light btn-outline-dark m-1" aria-label="Save" title="Save">
                                <i class="bi bi-floppy-fill"></i>
                            </button>
                        </form>
                    HTML;
                }
                ?>
            </div>
        </div>

        <div class="d-flex flex-row align-items-center mt-2 mb-2">
            <input class="form-control border border-dark" type="text" id="graphInput" placeholder="No node/edge selected."/>
        </div>

        <!-- Graph and editor sections-->
        <div class="d-flex p-3">
            <div id="graphDataHidden" hidden>
                <?php echo json_decode($graph["graph_data"]); ?>
            </div>
            <div id="graph" class="col-6 border" style="width: 50%; height: 600px;">

            </div>
            <div id="editor" class="col-6 border" style="min-height: 40vh; border-right: none;"><?php echo $graph["graph_code"]; ?></div>
        </div>

    </div>
</div>

<!-- Model used to display settings when settings button is pressed (settings button is only shown when the logged-in user is the owner) -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="?command=saveProject" method="POST">
            <div class="modal-content">
                <!-- Modal title-->
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Info of project-->
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?php echo $graph['title']; ?>" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="type">Project Type:</label>
                        <input type="text" class="form-control" id="type" name="type"
                               value="<?php echo $graph['graph_type']; ?>" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="description">Description:</label>
                        <input type="text" class="form-control" id="description" name="description"
                               value="<?php echo $graph['description']; ?>" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="owner">Owner:</label>
                        <input type="text" class="form-control" id="owner" name="owner"
                               value="<?php echo $graph['username']; ?>" disabled>
                    </div>
                    <input type="hidden" name="project_id" value="<?php echo $graph['project_id']; ?>">
                </div>

                <!-- Modal footer including delete, cancel, and save buttons-->
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <a href="?deleteProject=<?php echo $graph['project_id']; ?>" class="btn btn-danger">Delete Project</a>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div style="margin-top: 150px;"></div>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>
</body>
</html>
