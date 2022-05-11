<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';

if (!$_SESSION['admin']) header("Location:https://www.youtube.com/watch?v=dQw4w9WgXcQ");

if (!isset($_GET['r'])) echo 'Please stop messing with the URL :(';
else {

$refresh=(isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0');
if ($_GET['r']=='p'){
    $rPosts=true;
}
if ($_GET['r']=='u'){
    $rUsers=true;
}
if ($_GET['r']=='c'){
    $rComments=true;
}
if (!$refresh) {
    if (isset($_GET['a'])) {
        if ($_GET["a"] == "l") {
            likePost($_GET['v'], $_GET['p'], $username, $mysqli);
        } else if ($_GET["a"] == "d") {
            dislikePost($_GET['v'], $_GET['p'], $username, $mysqli);
        }
    }
    if (isset($_GET['bu'])){
        if ($_GET['bu']!=$username) blockUser($username,$_GET['bu'],$mysqli);
    }
    if (isset($_GET['ru'])){
        reportUser($username,$_GET['ru'],$mysqli);
    }
    if (isset($_GET['rp'])){
        reportPost($username,$_GET['rp'],$mysqli);
    }
    if (isset($_GET['co'])){
        if ($_SESSION['friendsonly']==0){
            $_SESSION['friendsonly']=1;
        }
        else $_SESSION['friendsonly']=0;
        header("Location:MainFeed.php");
    }
}

if ($_GET['r']=='p'){
    $stmt = $mysqli->prepare("SELECT *
    FROM posts
    WHERE Id in (
    Select reported from reportedposts
    where timesreported>=3
    )");
}
if ($_GET['r']=='u'){
    $stmt = $mysqli->prepare("SELECT *
    FROM profile
    WHERE Username in (
    Select Username from reportedusers
    where timesreported>=3
    )");
}
if ($_GET['r']=='c'){

}
if ($rPosts){
    $stmt = $mysqli->prepare("SELECT *
    FROM Users
    WHERE Username in (
    Select Username from reportedusers
    where timesreported>=3
    )");
}
else if ({
    $stmt = $mysqli->prepare("SELECT Id,Username,Text,Image,time
    FROM posts
    WHERE username!=? and username not in (
    Select Username2 from blocked
    where Username1=?
    union
    SELECT Username1 from blocked
    where Username2=?
       )
    ORDER BY time desc");
    $stmt->bind_param("sss", $username, $username, $username);
}

$stmt->execute();
$postsResult = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>

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

    document.addEventListener("DOMContentLoaded", function(event) {
            var scrollpos = localStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
    });

    window.onbeforeunload = function(e) {
        localStorage.setItem('scrollpos', window.scrollY);
    };

    function auto_grow(element) {
        element.style.height="1px";
        element.style.height=(element.scrollHeight)+"px";
    }


</script>

<html>
<head>
    <title>The Rock</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="PostStyle8.css">
    <link rel="stylesheet" href="NavbarStyle9.css">
    <link rel="stylesheet" href="MainFeedStyle.css">
</head>

<body>
<div class="navbar">
    <?php try {
        if (random_int(1, 10) == 7) {
            ?>
            <a href="MainFeed.php?fo=false"><img src="SiteSprites/TheRockLogoFinalDwayne.png" height="100%" alt="The Rock"></a>
        <?php } else { ?>
            <a href="MainFeed.php?fo=false"><img src="SiteSprites/TheRockLogoFinal.png" height=100% alt="The Rock"></a>
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
            <a href="AdminPage.php?r=p">Admin stuff</a>
        </div>
    </div>
    <?php
    if ($friendsOnly==1){
        ?>
        <div class="navbarButton active" onclick="location.href='MainFeed.php?fo=true'">
            Disable Friends Only Mode
        </div>
        <?php
    }
    else{
        ?>
        <div class="navbarButton" onclick="location.href='MainFeed.php?fo=false'">
            Enable Friends Only Mode
        </div>
        <?php
    }
    ?>
</div>