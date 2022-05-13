<?php

$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);


if (isset($_GET['d'])){
    if ($_GET['d']==0) {
        turnOffDarkMode($username,$mysqli);
        $darkmode=0;
    }
    else {
        turnOnDarkMode($username,$mysqli);
        $darkmode=1;
    }
}
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
    <?php if ($darkmode==0){ ?>
        <link rel="stylesheet" href="NavbarStyleLight.css">
        <link rel="stylesheet" href="DarkModePageStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="DarkModePageStyleDark.css">
    <?php } ?>
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
    <?php if ($darkmode==0){ ?>
        <div class="weaklingText">
            Just so you know, if you press this button you're a weakling.
        </div>
        <div class="darkModeButton" onclick="document.location.href='DarkModePage.php?d=1'">
            Turn on Dark Mode
        </div>
    <?php }
    else{ ?>
    <div class="weaklingText">
        Just so you know, if you don't press this button you're a terrible person.
    </div>
    <div class="darkModeButton" onclick="document.location.href='DarkModePage.php?d=0'">
        Turn off Dark Mode
    </div>
    <?php
    }
    ?>
</div>
</body>
</html>
