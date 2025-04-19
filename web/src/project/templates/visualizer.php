<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Will Baker (ppt4pq)">
    <title>Code Visualizer</title>

    <!-- Include bootstrap and main.css dependencies-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="res/styles/main.css">

    <!--Include support for ACE to render text as code -->
    <!--Source: https://ace.c9.io/ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>

    <!-- Include support for Vis.js network graph tool-->
    <!-- https://visjs.org/ -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

    <!-- Include support for Babel transpiler tool -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js" type="text/javascript"></script>

    <!-- Use 'main.js' file to create interactivity between DOM, ACE, and Vis.js -->
    <script src="res/scripts/babel-plugin.js" type="text/javascript"></script>
    <script src="res/scripts/main.js" type="text/javascript"></script>
</head>
<body>

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<div class="container" style="min-height: 100vh;">
    <!-- Show title and (if owner) show settings button-->
    <div class="d-flex justify-content-between align-items-center" style="margin-top: 100px">
        <h1 class="fw-bold m-2" style="color: rgb(207, 168, 115);">
            Project: <?php echo $graph['title']; ?>
        </h1>
        <!-- If owner, show settings button-->
        <?php
            if ($owns) {
                echo <<<HTML
                    <button type="button" class="btn btn-outline-dark" style="width: 80px; height: 40px;" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="bi bi-gear"></i>
                    </button>
                HTML;
            }
        ?>
    </div>

    <!--Show project information-->
    <div class="fluid-container d-flex align-items-center justify-content-center">
        <div class="card bg-light text-dark mb-3" style="width: 100%">
            <div class="card-body" style="width: 100%">
                <div class="row">
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
    </div>

    <!-- Display development environment -->
    <div class="card bg-light text-dark p-5">
        <!-- Task bar -->
        <div class="d-flex flex-row align-items-center">
            <!-- Show save button if currently-logged in user is owner-->
            <?php
                if ($owns) {
                    echo <<<HTML
                        <form id="submitForm" action="?command=saveProjectCode" method="POST">
                            <input type="hidden" name="project_id" id="project_id" value="
                    HTML;
                    echo $graph['project_id'];
                    echo <<<HTML
                         "><input type="hidden" name="editorContent" id="editorContent">
                            <button type="submit" class="btn btn-dark m-1">
                                Save
                            </button>
                        </form>
                    HTML;
                }
            ?>
        </div>

        <!-- Graph and editor sections-->
        <div class="d-flex p-3">
            <div id="graph" class="col-6 border p-3" style="width: 50%; height: 600px;">

            </div>
            <div id="editor" class="col-6 border" style="min-height: 40vh"><?php echo $graph["graph_code"]; ?></div>
        </div>

        <div class="d-flex flex-row align-items-center fs-1">
            <button id="add" class="btn btn-light btn-outline-dark m-1">Add</button>
            <button id="remove" class="btn btn-light btn-outline-dark m-1">Remove</button>
            <input class="form-control m-1" type="text" id="graphInput" placeholder="No node/edge selected."/>
        </div>

        <div class="d-flex flex-row align-items-center fs-1">
            <button id="physics" class="btn btn-light btn-outline-dark m-1">Turn physics on.</button>
            <button id="directed" class="btn btn-light btn-outline-dark m-1">Switch to a directed graph.</button>
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

<!--Include support for ACE to render text as code -->
<!--Source: https://ace.c9.io/ -->
<script src="https://cdn.jsdelivr.net/npm/ace-builds@1.39.1/src-noconflict/snippets/python.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/ace-builds@1.39.1/css/ace.min.css" rel="stylesheet">
<script>
    // Add support for ACE embedding onto the 'editor' div
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/javascript");

    // Allow the save button to update its hidden input to save the code to the database on
    document.getElementById("submitForm").onsubmit = function() {
        var content = editor.getValue();
        document.getElementById("editorContent").value = content;
    };
</script>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>
</body>
</html>
