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
</head>

<body class="body-with-background" style="background-image: url('res/background.png');">

<!-- Dynamically include header -->
<?php include('header.php'); ?>

<div class="fluid-container" style="display: flex; justify-content: center; align-items: center; height: 100vh">
    <!-- Sign-in Card -->
    <div class="card bg-dark text-light" style="min-width: 60%; max-width: 80%;">
        <div class="card-body d-flex flex-column align-items-center" style="padding-bottom: 5px;">
            <!-- Display message if it exists-->
            <div class="alert text-center <?php if ($isErrorMessage) { echo "alert-danger"; } else { echo "alert-success"; }?>"
                 role="alert" style="width: 75%; <?php if (empty($message)) { echo "display: none;"; } ?>">
                <?php echo $message; ?>
            </div>

            <!-- Display sign in text-->
            <h1 style="font-weight: bolder; color: rgb(207, 168, 115)">
                Sign-In
            </h1>

            <!-- Handle sign-in form logic-->
            <form action="?command=sign-in" method="POST" class="d-flex flex-column align-items-center">
                <input type="text" id="username" name="username" placeholder="Username: (Alphanumeric only)" class="form-control mt-1" style="min-width: 300px;">
                <input type="password" id="password" name="password" placeholder="Password:" class="form-control mt-1" style="min-width: 300px;">
                <button type="submit" id="submit-btn" class="btn btn-primary mt-3 mb-3 text-light"
                        style="background-color: #926b36; border-color: rgb(255, 255, 255);">
                    Sign-in
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Dynamically include footer -->
<?php include('footer.php'); ?>
</body>
</html>
