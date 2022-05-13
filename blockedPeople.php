<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);

$refresh=isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
$popup="";
if (!$refresh){
    if (isset($_GET['ub'])){
        unblockUser($username,$_GET['ub'],$mysqli);
        $unblocked=$_GET['ub'];
        $popup="$unblocked was blocked";
    }
}

$stmt = $mysqli->prepare("
SELECT Username,ProfilePicture FROM profile
WHERE Username!=? and Username in (
Select Username2 from blocked
where Username1=?
)");
$stmt->bind_param("ss", $username,$username);
$stmt->execute();
$blockedResult = $stmt->get_result();
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
    </script>

<html>
<head>
    <title>The Rock</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($darkmode==0){ ?>
        <link rel="stylesheet" href="FriendStyleLight.css">
        <link rel="stylesheet" href="NavbarStyleLight.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="blockedPeopleStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="FriendStyleDark.css">
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="blockedPeopleStyleDark.css">
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
</div>
<div class="popUpBox" style="display: <?php if ($popup=="") echo "none"; else echo "block"; ?>">
    <div class="popUpText">
        <?php echo $popup ?>
    </div>
</div>
<div class="middle">
    <?php
    if ($blockedResult->num_rows==0){
        ?>
        <div class="noBlockedText">
            <p> Looks like you have not blocked </p>
            <p> anyone yet :)</p>
            <a href="friendPage.php" style="color:#d7842c">Block People</a>
        </div>
        <?php
    }
    else{
        foreach ($blockedResult as $row){
            ?>
            <div class="friend">
                <div class="friendPicFrame">
                    <div class="friendProfilePic">
                        <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>"></a>
                    </div>
                </div>
                <div class="manageButtons">
                    <button class="oneButton" onclick="document.location='blockedPeople.php?ub=<?php echo $row['Username']?>'">Unblock</button>
                </div>
                <div class="friendUsername">
                    <a class="profileLink" href="profilePage.php?profile=<?php echo $row['Username'] ?>"><?php echo $row['Username']?></a>
                </div>
                <div class="mutualFriends">
                    <?php
                    $stmt = $mysqli->prepare("Select count(*) as friends from profile 
                    where Username not in (?,?) and Username in
                    ((
                    Select Username2 from friends
                    where Username1=? and Username2!=?
                    union
                    SELECT Username1 from friends
                    where Username2=? and Username1!=?
                    )intersect(
                    Select Username2 from friends
                    where Username1=? and Username2!=?
                    union
                    SELECT Username1 from friends
                    where Username2=? and Username1!=?
                    ))");
                    $stmt->bind_param("ssssssssss", $username,$row['Username'],$username,$row['Username'],$username,$row['Username'],$row['Username'],$username,$row['Username'],$username);
                    $stmt->execute();
                    $mutualFriendsResult = $stmt->get_result();
                    $stmt->close();
                    $mutualFriends=$mutualFriendsResult->fetch_assoc();
                    if ($mutualFriends["friends"]==1){
                        echo $mutualFriends["friends"]
                        ?>
                        mutual friend
                        <?php
                    }
                    else {
                        echo $mutualFriends["friends"]
                        ?>
                        mutual friends
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
    }
    ?>
</div>
</body>
</html>

<?php
    }
    ?>