<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Gabriel Jackson (tbp8gx), Will Baker (ppt4pq)">
    <title>Code Visualizer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="res/styles/main.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
</head>
<body>

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<!-- Show project info-->
<div class="container" style="min-height: 80vh;">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold m-3" style="color: rgb(207, 168, 115);">
            Project: <?php echo $graph['title']; ?>
        </h1>
        <button type="button" class="btn btn-outline-primary" style="width: 80px; height: 40px;" data-bs-toggle="modal" data-bs-target="#settingsModal">
            <i class="bi bi-gear"></i>
        </button>
    </div>


    <div class="fluid-container d-flex align-items-center justify-content-center">
        <div class="card bg-light text-dark mb-3" style="width: 80vw">
            <div class="card-body" style="width: 80vw">
                <div class="row">
                    <span class="graph-text-container">
                        <a href="?project_id=" class="graph-title-text">
                            <?php echo $graph['graph_type']; ?>
                        </a>
                    </span>
                    <span class="graph-text-container">
                        <span class="graph-owner-text">
                            <?php echo $graph['username']; ?>
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


    <form id="submitForm" action="?command=saveProjectCode" method="POST">
        <input type="hidden" name="project_id" id="project_id" value="<?php echo $graph['project_id']; ?>">
        <input type="hidden" name="editorContent" id="editorContent">
        <div class="row border">
            <div class="col-10"></div>
            <div class="col">
                <button class="btn btn-primary m-1">
                    Run
                </button>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary m-1">
                    Save
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-6 border">
                Hello
            </div>
            <div id="editor" class="col-6 border" style="min-height: 40vh; width: 50%;"><?php echo $graph["graph_code"]; ?></div>
        </div>
    </form>
</div>

<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="?command=saveProject" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
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
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/javascript");

    document.getElementById("submitForm").onsubmit = function() {
        var content = editor.getValue();
        document.getElementById("editorContent").value = content;
    };
</script>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>
</body>
</html>
