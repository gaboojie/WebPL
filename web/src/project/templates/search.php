<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Gabriel Jackson (tbp8gx)">
    <title>Project Search</title>

    <!-- Include bootstrap and main.css dependencies-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="res/styles/main.css">
</head>

<body>

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<div class="d-flex flex-column" style="min-height: 100vh;">
    <!-- Add search title -->
    <div class="container" style="padding-top: 100px;">
        <h1 class="fw-bold m-3" style="color: rgb(207, 168, 115);">
            <?php
            echo $searchTitle;
            ?>
        </h1>
    </div>


    <!--
    ---------------------------
    Render projects dynamically
    ---------------------------
    -->

    <?php
    if (!isset($graphs) || count($graphs) == 0) {
        if ($searchType == "AllProjects") {
            // If search type was scanning all projects and didn't find a match, tell the user no match was found for that query
            echo <<<HTML
                <div class="container d-flex align-items-center justify-content-center">
                    <div class="card bg-light text-dark m-3" style="width: 100%">
                        <div class="card-body m-3" style="width: 100%">
                            <h2>
                                Uh-oh! No results matched: 
                HTML;
            echo "'" . $searchQuery . "'";
            echo <<<HTML
                            </h2>
                        </div>
                    </div>
                </div>
            HTML;
        } else {
            // If search type was getting all of 'my' projects and didn't find a match, tell a user that they have not created a project yet!
            echo <<<HTML
                <div class="container d-flex align-items-center justify-content-center">
                    <div class="card bg-light text-dark m-3" style="width: 100%">
                        <div class="card-body m-3" style="width: 100%">
                            <h2>
                               You haven't made a project yet! <br> <br> Select the menu tab to create a new project! 
                            </h2>
                        </div>
                    </div>
                </div>
            HTML;
        }
    } else {
        // If projects exist, then for each project, dynamically create a vertical card list
        for ($i = 0; $i < count($graphs); $i++) {
            $graph = $graphs[$i];
            echo <<<HTML
                <div class="container d-flex align-items-center justify-content-center">
                    <div class="card bg-light text-dark mb-3" style="width: 100%">
                        <div class="card-body" style="width: 100%">
                            <div class="row">
                                <div class="col-3">
                                    <img class="img-fluid" id="graph-example-1" src="res/graph.png" alt="Graph Example Image 1">
                                </div>
                                <div class="col-9">
                                <span class="graph-text-container">
                                    <a href="?project_id=
                HTML;
            // Add project id for link
            echo $graph['project_id'];
            echo <<<HTML
                                    " class="graph-title-text">
                HTML;
            // Add project title
            echo $graph['title'];
            echo <<<HTML
                                    </a>
                                    <span class="graph-type-text">
                HTML;
            // Add project type
            echo $graph['graph_type'];
            echo <<<HTML
                                    </span>
                                </span>
                                    <span class="graph-text-container">
                                    <span class="graph-owner-text">
                HTML;
            // Add project creator
            echo 'By: ' . $graph['username'];
            echo <<<HTML
                                    </span>
                                </span>
                                    <span class="graph-text-container">
                                    <span class="graph-info-text">
                HTML;
            // Add project description
            echo $graph['description'];
            echo <<<HTML
                                    </span>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                HTML;
        }
    }
    ?>
</div>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>
</body>
</html>
