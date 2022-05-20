<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);

if (!$_SESSION['admin']) header("Location:https://www.youtube.com/watch?v=dQw4w9WgXcQ");

if (!isset($_GET['r'])) echo 'Please stop messing with the URL :(';
else {

$refresh=(isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0');
if (!$refresh) {
    if ($_GET['r']=='p'){
        if (isset($_GET['d'])) dismissPost($_GET['d'],$mysqli);
        if (isset($_GET['rp'])) {
            deletePost($_GET['rp'],$mysqli);
            giveStrike($_GET['rp'],$mysqli);
        }
    }
    if ($_GET['r']=='u'){
        if (isset($_GET['d'])) dismissUser($_GET['d'],$mysqli);
    }
    if ($_GET['r']=='c'){
        if (isset($_GET['d'])) dismissComment($_GET['d'],$mysqli);
        if (isset($_GET['rc'])){
            deleteComment($_GET['rc'],$mysqli);
            giveStrike($_GET['rc'],$mysqli);
        }
    }
    if (isset($_GET['b']) and isset($_GET['re'])){
        banUser($_GET['b'],$_GET['re'],$mysqli);
    }
    if (isset($_GET['a'])) {
        if (isset($_GET['v'])) {
            if ($_GET["a"] == "lp") {
                likePost($_GET['v'], $_GET['p'], $username, $mysqli);
            } else if ($_GET["a"] == "dp") {
                dislikePost($_GET['v'], $_GET['p'], $username, $mysqli);
            } else if (isset($_GET['c'])) {
                if ($_GET["a"] == "lc") {
                    likeComment($_GET['v'], $_GET['c'], $username, $mysqli);
                } else if ($_GET["a"] == "dc") {
                    dislikeComment($_GET['v'], $_GET['c'], $username, $mysqli);
                }
            }
        }
    }
}

if ($_GET['r']=='p'){
    $stmt = $mysqli->prepare("Select * from posts where id in (
    select reported
    from reportedposts
    group by reported
    having count(reporter) >=3
    order by count(reporter) desc
    )");
}
if ($_GET['r']=='u'){
    $stmt = $mysqli->prepare("Select * from profile where Username in (
    select reported
    from reportedusers
    group by reported
    having count(reporter) >=3
    order by count(reporter) desc)");
}
if ($_GET['r']=='c'){
    $stmt = $mysqli->prepare("Select * from comments where cid in (
select reported
    from reportedcomments
    group by reported
    having count(reporter) >=3
    order by count(reporter) desc)");
}
$stmt->execute();
$reportResult = $stmt->get_result();
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
    <?php if ($darkmode==0){ ?>
        <link rel="stylesheet" href="PostStyleLight.css">
        <link rel="stylesheet" href="commentPageStyleLight.css">
        <link rel="stylesheet" href="FriendStyleLight.css">
        <link rel="stylesheet" href="NavbarStyleLight.css">
        <link rel="stylesheet" href="AdminPageStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="PostStyleDark.css">
        <link rel="stylesheet" href="commentPageStyleDark.css">
        <link rel="stylesheet" href="FriendStyleDark.css">
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="AdminPageStyleDark.css">
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
            <a href="AdminPage.php?r=p">Admin stuff</a>
        </div>
    </div>
    <div class="navbarButton<?php if ($_GET['r']=='p') echo " active" ?>" onclick="location.href='AdminPage.php?r=p'">
        Reported Posts
    </div>
    <div class="navbarButton multiple <?php if ($_GET['r']=='u') echo " active" ?>" onclick="location.href='AdminPage.php?r=u'">
        Reported Users
    </div>
    <div class="navbarButton multiple <?php if ($_GET['r']=='c') echo " active" ?>" onclick="location.href='AdminPage.php?r=c'">
        Reported Comments
    </div>
</div>
<div class="bigMiddle">
    <div class="banbox" id="banbox" style="display:<?php if (isset($_GET['b']) and !isset($_GET['re'])) echo "block"; else echo "none"; ?>">
        <h1> Are you sure you want to ban <br> <?php echo $_GET['b']?> ? </h1>
        <textarea class="reasonbox" id="reasonbox" cols="70" rows="4" placeholder="Reason for ban"></textarea>
        <div class="banButton" onclick="document.location.href='AdminPage.php?r=<?php echo $_GET['r'] ?>'">Cancel</div>
        <div class="banButton" onclick="document.location.href='AdminPage.php?r=<?php echo $_GET['r'] ?>&b=<?php echo $_GET['b'] ?>&re='+document.getElementById('reasonbox').value">Ban</div>
    </div>
    <div class="smolMiddle">
    <?php
    if ($reportResult->num_rows==0){
        ?>
        <div class="noReportsText">
            <p> Looks like nothing is sus. </p>
            <p> Or at least people haven't found it yet.</p>
        </div>
        <?php
    }
    else{
        if ($_GET['r']=='p'){
            $counter=0;
            foreach ($reportResult as $row){
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
                <div class="buttonBar">
                    <div class="adminButton" onclick="document.location.href='AdminPage.php?r=p&d=<?php echo $row['Id'] ?>'">
                        Dismiss
                    </div>
                    <div class="adminButton" onclick="document.location.href='AdminPage.php?r=p&rp=<?php echo $row['Id'] ?>'">
                        Remove Post
                    </div>
                    <div class="adminButton" onclick="document.location.href='AdminPage.php?r=p&b=<?php echo $row['Username'] ?>'">
                        Ban User
                    </div>
                    <div class="striketext">
                        <?php echo getStrikes($row['Username'],$mysqli) ?> Strikes
                    </div>
                </div>
                <div class="post">
                    <div class="namebar">
                        <div class="postProfilePicFrame">
                            <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="postProfilePic" src="ProfileAndPostPics/<?php echo $profilePic?>" height="100%"></a>
                        </div>
                        <div class="postUsername">
                            <a class="profileLink" href="profilePage.php?profile=<?php echo $row['Username'] ?>"><?php echo $row['Username']?></a>
                        </div>
                        <div class="postOptions">
                            <img src="SiteSprites/options.png">
                            <div class="postOptions-content">
                                <a href="?bu=<?php echo $row['Username']?>">Block User</a>
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
                                <a href="?r=p&a=lp&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/like.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?r=p&a=dp&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/dislike.png"></a>
                            </div>
                            <?php
                        }
                        else if ($likeValue==1){
                            ?>
                            <div class="likeButton">
                                <a href="?r=p&a=lp&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?r=p&a=dp&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/dislike.png"></a>
                            </div>
                            <?php
                        }
                        else {
                            ?>
                            <div class="likeButton">
                                <a href="?r=p&a=lp&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/like.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?r=p&a=dp&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
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
        }
        else if ($_GET['r']=='u'){
            foreach ($reportResult as $row){
                ?>
                <div class="buttonBar">
                    <div class="adminButton" onclick="document.location.href='AdminPage.php?r=u&d=<?php echo $row['Username'] ?>'">
                        Dismiss
                    </div>
                    <div class="adminButton" onclick="document.location.href='AdminPage.php?r=u&b=<?php echo $row['Username'] ?>'">
                        Ban User
                    </div>
                    <div class="striketext">
                        <?php echo getStrikes($row['Username'],$mysqli) ?> Strikes
                    </div>
                </div>
                <div class="friend">
                    <div class="friendPicFrame">
                        <div class="friendProfilePic">
                            <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>"></a>
                        </div>
                    </div>
                    <div class="friendUsername">
                        <a class="profileLink" href="profilePage.php?profile=<?php echo $row['Username'] ?>"><?php echo $row['Username']?></a>
                    </div>
                </div>
                <?php
            }
        }
        else if ($_GET['r']=='c') {
            ?>
                <?php
                $counter=0;
                foreach ($reportResult as $row){
                    $counter++;
                    $stmt=$mysqli->prepare("SELECT ProfilePicture FROM profile where Username=?");
                    $stmt->bind_param("s", $row['Username']);
                    $stmt->execute();
                    $commenterPic = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $stmt=$mysqli->prepare("SELECT * FROM posts where Id=?");
                    $stmt->bind_param("i", $row['pid']);
                    $stmt->execute();
                    $postResult = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $stmt = $mysqli->prepare("SELECT likeValue FROM likedcomments where Username=? and cid=?");
                    $stmt->bind_param("si", $username,$row['cid']);
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
                    <div class="buttonBar">
                        <div class="adminButton" onclick="document.location.href='commentPage.php?p=<?php echo $row['pid'] ?>'">
                            View Post
                        </div>
                        <div class="adminButton" onclick="document.location.href='AdminPage.php?r=c&d=<?php echo $row['cid'] ?>'">
                            Dismiss
                        </div>
                        <div class="adminButton" onclick="document.location.href='AdminPage.php?r=c&rp=<?php echo $row['cid'] ?>'">
                            Remove Comment
                        </div>
                        <div class="adminButton" onclick="document.location.href='AdminPage.php?r=c&b=<?php echo $row['Username'] ?>'">
                            Ban User
                        </div>
                        <div class="striketext">
                            <?php echo getStrikes($row['Username'],$mysqli) ?> Strikes
                        </div>
                    </div>
                        <div class="post">

                    <div class="comment">
                        <div class="commentPPicFrame">
                            <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="commentPPic" src="ProfileAndPostPics/<?php echo $commenterPic['ProfilePicture']?>"></a>
                        </div>
                        <div class="commentOptions">
                            <img src="SiteSprites/options.png">
                            <?php
                            if ($username==$row['Username']){
                                ?>
                                <div class="commentOptions-content">
                                    <a href="?p=<?php echo $row['pid']?>&dc=<?php echo $row['cid']?>">Delete Comment</a>
                                </div>
                                <?php
                            }
                            else{
                                ?>
                                <div class="commentOptions-content">
                                    <a href="?p=<?php echo $row['pid']?>&bu=<?php echo $row['Username']?>">Block User</a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="commentBody">
                            <div class="commentUser">
                                <?php
                                if ($postResult['Username']==$row['Username']){
                                    ?>
                                    <a href="profilePage.php?profile=<?php echo $row['Username'] ?>" style="text-decoration: none; color: #d7842c"><?php echo $row['Username']?></a>
                                    <?php
                                }
                                else{
                                    ?>
                                    <a href="profilePage.php?profile=<?php echo $row['Username'] ?>" style="text-decoration: none; color: #6e6e6e"><?php echo $row['Username']?></a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            if ($row['Text']!=''){
                                ?>
                                <textarea readonly id="com<?php echo $counter?>" class="commentText" cols="70"><?php echo $row['Text']?></textarea>
                                <script>
                                    auto_grow(document.getElementById('com<?php echo $counter?>'));
                                </script>
                                <?php
                            }
                            if ($row['Image']!=''){
                                ?>
                                <div class="commentImage">
                                    <img src="ProfileAndPostPics/<?php echo $row['Image'] ?>">
                                </div>
                                <?php
                            }
                            ?>
                            <div class="commentLikeBar">
                                <?php
                                if ($likeValue==0){ ?>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=lc&c=<?php echo $row['cid']?>&v=0"><img src="SiteSprites/like.png"></a>
                                    </div>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=dc&c=<?php echo $row['cid']?>&v=0"><img src="SiteSprites/dislike.png"></a>
                                    </div>
                                    <?php
                                }
                                else if ($likeValue==1){
                                    ?>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=lc&c=<?php echo $row['cid']?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                                    </div>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=dc&c=<?php echo $row['cid']?>&v=1"><img src="SiteSprites/dislike.png"></a>
                                    </div>
                                    <?php
                                }
                                else {
                                    ?>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=lc&c=<?php echo $row['cid']?>&v=-1"><img src="SiteSprites/like.png"></a>
                                    </div>
                                    <div class="commentLikeButton">
                                        <a href="?r=c&p=<?php echo $row['pid']?>&a=dc&c=<?php echo $row['cid']?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="commentLikeNumber">
                                    <?php echo getCommentLikes($row['cid'],$mysqli) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space"></div>
                <?php
                }
                ?>

            <?php
        }
            ?>

        <?php
    }
    ?>
    </div>
</div>
</body>
</html>
<?php
}
?>