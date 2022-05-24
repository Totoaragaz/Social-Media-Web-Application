<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
$friendsOnly = $_SESSION['friendsonly'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);

$refresh=(isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0');
$popup="";
if (!$refresh) {
    if (isset($_GET['a'])) {
        if ($_GET["a"] == "l") {
            likePost($_GET['v'], $_GET['p'], $username, $mysqli);
        } else if ($_GET["a"] == "d") {
            dislikePost($_GET['v'], $_GET['p'], $username, $mysqli);
        }
    }
    if (isset($_GET['bu'])){
        if ($_GET['bu']!=$username) {
            blockUser($username,$_GET['bu'],$mysqli);
            $blocked=$_GET['bu'];
            $popup="$blocked was blocked";
        }
    }
    if (isset($_GET['ru'])){
        reportUser($username,$_GET['ru'],$mysqli);
        $popup="Thank you for your report! Our admins will have a look.";
    }
    if (isset($_GET['rp'])){
        reportPost($username,$_GET['rp'],$mysqli);
        $popup="Thank you for your report! Our admins will have a look.";
    }
    if (isset($_GET['fo'])){
        if ($_SESSION['friendsonly']==0){
            $_SESSION['friendsonly']=1;
        }
        else $_SESSION['friendsonly']=0;
        header("Location:MainFeed.php");
    }
}
if ($friendsOnly==1){
    if ($_SESSION['admin']){
        $stmt = $mysqli->prepare("SELECT Id,Username,Text,Image,time
        FROM posts
        WHERE username!=? and username in (
        Select Username2 from friends
        where Username1=?
        union
        SELECT Username1 from friends
        where Username2=?
        )
        ORDER BY time desc");
        $stmt->bind_param('sss', $username, $username, $username);
    }
    else {
        $stmt = $mysqli->prepare("SELECT Id,Username,Text,Image,time
        FROM posts
        WHERE username!=? and username not in (
        Select Username2 from blocked
        where Username1=?
        union
        SELECT Username1 from blocked
        where Username2=?
           )
        and username in (
        Select Username2 from friends
        where Username1=?
        union
        SELECT Username1 from friends
        where Username2=?
        )
        ORDER BY time desc");
        $stmt->bind_param('sssss', $username, $username, $username, $username, $username);
    }
}
else{
    if ($_SESSION['admin']){
        $stmt = $mysqli->prepare("SELECT Id,Username,Text,Image,time
            FROM posts
            WHERE username!=?
            ORDER BY time desc");
        $stmt->bind_param('s', $username);
    }
    else {
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
        if (localStorage.getItem('friendsonly')!==<?=$friendsOnly?>) {
            var scrollpos = localStorage.getItem('scrollpos');
            if (scrollpos) window.scrollTo(0, scrollpos);
        }
    });

    window.onbeforeunload = function(e) {
        localStorage.setItem('scrollpos', window.scrollY);
    };

    localStorage.setItem('friendsonly',<?=$friendsOnly?>);

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
    <?php if ($darkmode==0){ ?>
        <link rel="stylesheet" href="PostStyleLight.css">
        <link rel="stylesheet" href="NavbarStyleLight.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="MainFeedStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="PostStyleDark.css">
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="MainFeedStyleDark.css">
    <?php } ?>
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
        <a class="logout" href="logout.php">Log out</a>
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
<div class="popUpBox" style="display: <?php if ($popup=="") echo "none"; else echo "block"; ?>">
    <div class="popUpText">
        <?php echo $popup ?>
    </div>
</div>
<div class="middle">
    <?php
    if ($postsResult->num_rows==0){
        ?>
        <div class="noFriendsText">
            <p> Looks like you don't have friends or they </p>
            <p>haven't posted anything yet :(</p>
            <a href="friendPage.php" style="color:#d7842c">Add friends</a>
        </div>
        <?php
    }
    else{
        $counter=0;
        foreach ($postsResult as $row){
            $counter++;
            $stmt = $mysqli->prepare("SELECT ProfilePicture FROM profile where Username=?");
            $stmt->bind_param("s", $row['Username']);
            $stmt->execute();
            $postUserResult = $stmt->get_result();
            $stmt->close();

            if ($postUserResult->num_rows==0) echo "Something went wrong";
            else{
                $userRow=$postUserResult->fetch_assoc();
                $profilePic = $userRow["ProfilePicture"];
            }
            $stmt = $mysqli->prepare("SELECT likeValue FROM likedposts where Username=? and pid=?");
            $stmt->bind_param("si", $username,$row['Id']);
            $stmt->execute();
            $likeResult = $stmt->get_result();
            $stmt->close();
            if ($likeResult->num_rows!=0) {
                $likeResultRow = $likeResult->fetch_assoc();
                $likeValue = $likeResultRow['likeValue'];
            }
            else{
                $likeValue=0;
            }
            ?>
            <div class="post">
                <div class="namebar">
                    <div class="postProfilePicFrame">
                        <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="postProfilePic" src="ProfileAndPostPics/<?php echo $profilePic?>" height="100%"></a>
                    </div>
                    <div class="postUsername">
                        <a class="profileLink" href="profilePage.php?profile=<?php echo $row['Username'] ?>"><?php echo $row['Username']?></a>
                    </div>
                    <div class="postOptions">
                        <img src="SiteSprites/options<?php if ($darkmode==1) echo "Dark"?>.png">
                        <div class="postOptions-content">
                            <a href="?bu=<?php echo $row['Username']?>">Block User</a>
                            <a href="?rp=<?php echo $row['Id']?>">Report Post</a>
                            <a href="?ru=<?php echo $row['Username']?>">Report User</a>
                            <?php
                            if ($_SESSION['admin']){
                                ?>
                                <a href="AdminPage.php?r=p&b=<?php echo $row['Username']?>">Ban User</a>
                                    <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="postDate">
                        <?php echo $row['time']?>
                    </div>
                </div>
                <div class="actualPost">
                    <a href="commentPage.php?p=<?php echo $row['Id']?>" style="text-decoration: none; color:black">
                    <textarea readonly id="pcap<?php echo $counter?>" class="postCaption" cols="70" rows="20"><?php echo $row['Text']?></textarea>
                    <script>
                        auto_grow(document.getElementById('pcap<?php echo $counter?>'));
                    </script>
                    <?php
                    if ($row['Image']!=''){
                        ?>
                            <img class="postImage" src="ProfileAndPostPics/<?php echo $row['Image'] ?>">
                        <?php
                    }
                    ?>
                    </a>
                </div>
                <div class="likeBar">
                    <?php
                        if ($likeValue==0){ ?>
                            <div class="likeButton">
                                <a href="?a=l&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/like<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?a=d&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/dislike<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <?php
                        }
                        else if ($likeValue==1){
                            ?>
                            <div class="likeButton">
                                <a href="?a=l&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?a=d&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/dislike<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <?php
                        }
                        else {
                            ?>
                            <div class="likeButton">
                                <a href="?a=l&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/like<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?a=d&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
                            </div>
                            <?php
                            }
                        ?>
                        <div class="likeNumber">
                            <?php echo getPostLikes($row['Id'],$mysqli) ?>
                        </div>
                        <div class="comments">
                            <a href="commentPage.php?p=<?php echo $row["Id"] ?>" style="text-decoration: none; color:#a6a6a6">
                                <?php
                                $stmt = $mysqli->prepare("SELECT count(cid) as 'count' FROM comments where pid=?");
                                $stmt->bind_param("i", $row['Id']);
                                $stmt->execute();
                                $comments = $stmt->get_result();
                                $stmt->close();
                                $comments=$comments->fetch_assoc();
                                if ($comments['count']==1) echo $comments['count']." Comment";
                                else echo $comments['count']." Comments";
                                ?>
                            </a>
                        </div>
                </div>
            </div>
            <?php
        }
        ?>
    <div class="endOfFeed">
        That's all we got :(
    </div>
    <?php
    }
    ?>
</div>
</body>
</html>
