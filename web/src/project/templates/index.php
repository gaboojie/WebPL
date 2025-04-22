<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Project URL: cs4640.cs.virginia.edu/tbp8gx/project/index.php -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Gabriel Jackson (tbp8gx)">
    <title>
        Graph Visualizer
    </title>

    <!-- Include bootstrap and main.css dependencies-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="res/styles/main.css">

    <!-- Include support for Vis.js network graph tool-->
    <!-- Source: https://visjs.org/ -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
</head>

<body id="body">

<div id="bg-graph"></div>

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<!--Landing page body-->
<div class="fluid-container" style="display: flex; justify-content: center; align-items: center; height: 100vh">
    <div class="card bg-dark text-light" style="min-width: 60%; max-width: 80%;">
        <div class="card-body d-flex flex-column align-items-center m-3">
            <!-- Title -->
            <h1 style="font-weight: bolder;">
                <span class="text-light">Graph</span>
                <span style="color: rgb(207, 168, 115)">Visualizer</span>
            </h1>

            <!-- Search Box -->
            <form action="index.php" method="GET" class="d-flex align-items-center">
                <input type="text" id="search" name="search" class="form-control" placeholder="Search for graphs..."
                       aria-label="Search for graphs" aria-describedby="graph-search" required>
                <button type="submit" class="btn">
                    <i class="bi bi-search" style="color: rgb(207, 168, 115);"></i>
                </button>
            </form>
        </div>


        <!-- Display sign in if user is not signed in-->
        <?php
        if (!isset($_SESSION['user_id'])) {
            echo <<<END
                    <!-- Sign-In -->
                    <div class="card-body d-flex flex-column align-items-center">
                        <strong style="padding-bottom: 10px;">
                            Or:
                        </strong>
                        <a id="sign-in" href="?command=signinpage" class="btn btn-outline-light">
                            <span>
                                 Sign In
                            </span>
                            <i class="bi bi-person-circle ms-2"></i>
                        </a>
                        <h6 style="margin-top: 10px;">
                            To create your own!
                        </h6>
                    </div>
                END;
        }
        ?>
    </div>
</div>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>

</body>
<script type="text/javascript" src="res/scripts/movingGraph.js"></script>
</html>