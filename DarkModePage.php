<?php

$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';

?>
<!DOCTYPE html>

<html>
<head>
    <script>
        localStorage.openpages = Date.now();
        var onLocalStorageEvent = function(e){
            if(e.key == "openpages"){
                localStorage.page_available = Date.now();
            }
            if(e.key == "page_available"){
                alert("Servers are full right now, please try again later.");
                window.location.href="https://www.youtube.com/watch?v=dQw4w9WgXcQ";
            }
        };
        window.addEventListener('storage', onLocalStorageEvent, false);
    </script>
    <title>The Rock</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="NavbarStyle9.css">
    <link rel="stylesheet" href="DarkModePageStyle.css">
</head>

<body>
<div class="navbar">
    <?php try {
        if (random_int(1, 10) == 7) {
            ?>
            <a href="MainFeed.php"><img src="SiteSprites/TheRockLogoFinalDwayne.png" height="100%" alt="The Rock"></a>
        <?php } else { ?>
            <a href="MainFeed.php"><img src="SiteSprites/TheRockLogoFinal.png" height=100% alt="The Rock"></a>
        <?php }
    } catch (Exception $e) {
    } ?>
    <a class="notactive" href="profilePage.php?profile=<?php echo $username ?>">Profile</a>
    <a class="notactive" href="friendPage.php">Friends</a>
    <a class="logout" href="index.php">Log out</a>
    <div class="navbaroptions">
        Options
        <div class="navbaroptions-content">
            <a href="blockedPeople.php">Unblock people</a>
            <a href="DarkModePage.php">Dark Mode</a>
            <?php
            if ($_SESSION['admin']){
                ?>
                <a href="AdminPage.php?r=p">Admin stuff</a>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<div class="middle">
    <div class="weaklingText">
        Just so you know, if you press this button you're a weakling.
    </div>
    <div class="darkModeButton">
        Toggle Dark Mode
    </div>
</div>
</body>
</html>
