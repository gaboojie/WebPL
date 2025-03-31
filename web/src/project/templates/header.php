<nav class="navbar navbar-expand-lg navbar-light bg-dark">
    <div class="container-fluid">
        <!-- Drop down link-->
        <div class="dropdown m-3 mr-auto">
            <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Graph Visualizer
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                <?php
                if (isset($_SESSION['user_id'])) {
                    echo <<<END
                        <li>
                            <a class="dropdown-item" href="?command=landing">Home</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?command=myprojects">My Projects</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?command=createproject">Create Project</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?command=logout">Log out</a>
                        </li>
                    END;
                } else {
                    echo <<<END
                        <li>
                            <a class="dropdown-item" href="?command=landing">Home</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?command=signinpage">Sign-in</a>
                        </li>
                    END;
                }
                ?>


            </ul>
        </div>

        <!-- Search box -->
        <form action="index.php" method="GET" class="d-flex align-items-center">
            <input type="text" id="search" name="search" class="form-control" placeholder="Search for graphs..."
                   aria-label="Search for graphs" aria-describedby="graph-search" required>
            <button type="submit" class="btn">
                <i class="bi bi-search" style="color: rgb(207, 168, 115);"></i>
            </button>
        </form>
    </div>
</nav>
