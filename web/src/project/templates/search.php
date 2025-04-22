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

<input type="hidden" id="searchQuery" value="<?php if (isset($searchQuery)) { echo $searchQuery; }?>">

<div id="projectList" class="d-flex flex-column" style="min-height: 100vh;">
    <!-- Add search title -->
    <div class="container" style="padding-top: 100px;">
        <h1 id="searchTitle" class="fw-bold m-3" style="color: rgb(207, 168, 115);"><?php echo $searchTitle;?></h1>
    </div>
</div>
<!-- Dynamically include footer -->
<?php include('footer.php'); ?>

<script>
    function updateDOMWithProjectsList(data) {
        const projectListElement = document.getElementById("projectList");

        // Add search title
        projectListElement.innerHTML =
            `<div class="container" style="padding-top: 100px;">
                <h1 id="searchTitle" class="fw-bold m-3" style="color: rgb(207, 168, 115);"><?php echo $searchTitle;?></h1>
            </div>`;

        // Add projects to DOM
        for (let i = 0; i < data.length; i++) {
            const project = data[i];
            projectListElement.innerHTML += `
            <div class="container d-flex align-items-center justify-content-center">
                <div class="card bg-light text-dark mb-3" style="width: 100%">
                    <div class="card-body" style="width: 100%">
                        <div class="row">
                            <div class="col-3">
                                <img class="img-fluid" id="graph-example-1" src="res/graph.png" alt="Graph Example Image 1">
                            </div>
                            <div class="col-9">
                                <span class="graph-text-container">
                                    <a href="?project_id=${project.project_id}" class="graph-title-text">
                                        ${project.title}
                                    </a>
                                    <span class="graph-type-text">${project.type}</span>
                                </span>
                                <span class="graph-text-container">
                                    <span class="graph-owner-text">${project.owner_username}</span>
                                </span>
                                <span class="graph-text-container">
                                    <span class="graph-info-text">${project.description}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        // Add invisible margin if a lot of elements are being displayed
        if (data.length > 5) {
            projectListElement.innerHTML += `<div style="margin-top: 100px"></div>`;
        }
    }

    function updateDOMWithEmptyProjectsList() {
        const searchTitle = document.getElementById('searchTitle').textContent;
        let isOwner = (searchTitle === "Your Projects:");

        // Add search title
        const projectListElement = document.getElementById("projectList");
        projectListElement.innerHTML =
            `<div class="container" style="padding-top: 100px;">
                <h1 id="searchTitle" class="fw-bold m-3" style="color: rgb(207, 168, 115);"><?php echo $searchTitle;?></h1>
            </div>`;

        if (isOwner) {
            // If you are in the owner view, display owner text
            projectListElement.innerHTML +=
            `
                <div class="container d-flex align-items-center justify-content-center">
                    <div class="card bg-light text-dark m-3" style="width: 100%">
                        <div class="card-body m-3" style="width: 100%">
                            <h2>
                               You haven't made a project yet! <br> <br> Select the menu tab to create a new project!
                            </h2>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // If you are not in owner view, display no projects found text
            const searchQuery = document.getElementById('searchQuery').value;
            projectListElement.innerHTML +=
            `
            <div class="container d-flex align-items-center justify-content-center">
                <div class="card bg-light text-dark m-3" style="width: 100%">
                        <div class="card-body m-3" style="width: 100%">
                            <h2>
                                Uh-oh! No results matched: ${searchQuery}
                            </h2>
                        </div>
                    </div>
            </div>
            `;
        }
    }

    function updateProjectsList(projectData) {
        if (projectData.length === 0) {
            updateDOMWithEmptyProjectsList();
        } else {
            updateDOMWithProjectsList(projectData);
        }
    }

    // This function will request the server to getProjectsThatMatch() according to if they are under the 'my projects' view or searching for projects
    function getProjectsThatMatch() {
        // Determine if we are using my projects or no projects
        const searchTitle = document.getElementById('searchTitle').textContent;
        let isOwner = (searchTitle === "Your Projects:") ? "true" : "false";

        // Get search data if not currently on owner view
        let searchQuery = '';
        if (isOwner === "false") {
            searchQuery = document.getElementById('searchQuery').value;
        }

        // Create request
        const request = new XMLHttpRequest();
        const url = "/project/?command=getMatchingProjects";
        request.open('POST', url, true);
        request.setRequestHeader('Accept', 'application/json;');

        // Handle request once data is returned
        request.onload = () => {
            if (request.status === 200) {
                const data = JSON.parse(request.responseText);
                updateProjectsList(data);
            } else {
                console.log('Request status failed.');
            }
        };

        // Handle any request errors
        request.onerror = () => {
            console.log("Error fetching project data!");
        };

        // Create form data and send request
        const formData = new FormData();
        formData.append('isOwner', isOwner);
        formData.append('searchQuery', searchQuery);
        formData.append('maxProjects', '20');
        request.send(formData);
    }
    getProjectsThatMatch();
</script>

</body>
</html>
