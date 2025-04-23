<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Will Baker (ppt4pq)">
    <title>Sign-in</title>

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

<div class="fluid-container" style="display: flex; justify-content: center; align-items: center; height: 90vh">
    <!-- Sign-in Card -->
    <div class="card bg-dark text-light" style="min-width: 60%; max-width: 80%;">
        <div class="card-body d-flex flex-column align-items-center" style="padding-bottom: 5px;">
            <!-- Display message if it exists-->
            <div class="m-3 alert text-center <?php if ($isErrorMessage) { echo "alert-danger"; } else { echo "alert-success"; }?>"
                 role="alert" style="width: 75%; <?php if (empty($message)) { echo "display: none;"; } ?>">
                <?php echo $message; ?>
            </div>

            <!-- Display sign in text-->
            <h1 style="font-weight: bolder; color: rgb(207, 168, 115)">
                Sign-In
            </h1>

            <!-- Handle sign-in form logic-->
            <form action="?command=sign-in" method="POST" class="d-flex flex-column align-items-center">
                <input type="text" id="username" name="username" placeholder="Username:" class="form-control mt-1" style="width: 300px;">
                <input type="password" id="password" name="password" placeholder="Password:" class="form-control mt-1 mb-1" style="width: 300px;">
                <p id="warning" class="mt-3 transparent-text"></p>
                <button type="submit" id="submit-btn" class="btn btn-primary mt-2 mb-3 text-light"
                        style="background-color: #926b36; border-color: rgb(255, 255, 255);">
                    Sign-in
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>

<script>
    const usernameElement = document.getElementById('username');
    const passwordElement = document.getElementById('password');
    const warningElement = document.getElementById("warning");

    function showWarningText(value) {
        warningElement.textContent = "Warning: " + value;
        warningElement.classList.remove('transparent-text');
        warningElement.classList.add('warning-text');
    }

    function hideWarningText() {
        warningElement.classList.add('transparent-text');
        warningElement.classList.remove('warning-text');
    }

    function updateInput() {
        const isValidInput = /^[a-zA-Z0-9]+$/.test(usernameElement.value) && passwordElement.value.length >= 8;
        if (isValidInput) {
            hideWarningText();
        } else {
            if (passwordElement.value.length === 0) {
                showWarningText("You must specify a password!");
            } else if (usernameElement.value.length === 0) {
                showWarningText("You must specify a username!");
            } else if (!(/^[a-zA-Z0-9]+$/.test(usernameElement.value))) {
                showWarningText("Your username must be alphanumeric!");
            } else {
                showWarningText("Your password is weak.");
            }
        }
    }
    // Add element listeners
    usernameElement.addEventListener("input", updateInput);
    passwordElement.addEventListener("input", updateInput);
</script>
<script type="text/javascript" src="res/scripts/movingGraph.js"></script>
</body>
</html>
