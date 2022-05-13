<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);


if (!isset($_GET['p'])) echo "Please stop messing with the URL :(";
else{
$pid=$_GET['p'];

    $stmt=$mysqli->prepare("SELECT Username,Text,Image,time FROM posts where Id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $postResult = $stmt->get_result();
    $stmt->close();
    $postResult=$postResult->fetch_assoc();

    $popup="";
    $commtext="";
    $commimg="";
    $refresh=isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
    if (!$refresh) {
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
            else if ($_GET['a']=="p" and isset($_GET['ct']) and isset($_GET['ci'])){
                makeComment($pid,$username,$_GET['ct'],$_GET['ci'],$mysqli);
                $popup="Comment was posted.";
            }
        }
        if (isset($_GET['bu'])){
            blockUser($username,$_GET['bu'],$mysqli);
            $blocked=$_GET['bu'];
            $popup="$blocked was blocked.";
        }
        if (isset($_GET['ru'])){
            reportUser($username,$_GET['ru'],$mysqli);
            $popup="Thank you for your report! Our admins will have a look.";
        }
        if (isset($_GET['rp'])){
            reportPost($username,$_GET['rp'],$mysqli);
            $popup="Thank you for your report! Our admins will have a look.";
        }
        if (isset($_GET['de']) and ($postResult['Username']==$username or $_SESSION['admin'])){
            deletePost($_GET['de'],$mysqli);
            $popup="Post was deleted.";
        }
        if (isset($_GET['dc'])){
            deleteComment($_GET['dc'],$mysqli);
            $popup="Comment was deleted.";
        }
        if (isset($_GET['rc'])){
            reportComment($username,$_GET['rc'],$mysqli);
            $popup="Thank you for your report! Our admins will have a look.";
        }
    }
    if (isset($_GET['ci']) and isset($_GET['ct']) and !isset($_GET['a'])){
        $commtext=$_GET['ct'];
        $commimg=$_GET['ci'];
    }

$stmt=$mysqli->prepare("SELECT ProfilePicture FROM profile where Username=?");
$stmt->bind_param("s", $postResult['Username']);
$stmt->execute();
$posterPPic = $stmt->get_result();
$stmt->close();
$posterPPic=$posterPPic->fetch_assoc();

    $stmt=$mysqli->prepare("SELECT ProfilePicture FROM profile where Username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $userPPic = $stmt->get_result();
    $stmt->close();
    $userPPic=$userPPic->fetch_assoc();

$stmt=$mysqli->prepare("SELECT likeValue FROM likedposts where Username=? and pid=?");
$stmt->bind_param("si", $username,$pid);
$stmt->execute();
$postlike = $stmt->get_result();
$stmt->close();
if ($postlike->num_rows!=0){
    $postlike=$postlike->fetch_assoc();
    $postlikeValue=$postlike['likeValue'];
}
else $postlikeValue=0;

$stmt=$mysqli->prepare("SELECT cid,Username,Text,Image,time FROM comments WHERE pid=? and Username not in(
Select Username2 from blocked
where Username1=?
union
SELECT Username1 from blocked
where Username2=?
)
order by time");
    $stmt->bind_param("iss",$pid,$username,$username);
    $stmt->execute();
    $commentsResult = $stmt->get_result();
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

    window.onbeforeunload = function(e){
        localStorage.setItem('scrollpos', window.scrollY);
    };

    function auto_grow(element) {
        element.style.height="1px";
        element.style.height=(element.scrollHeight)+"px";
    }

    function uploadImage(){
        const pid='<?=$pid?>';
        const img=document.getElementById('commimg').value.replace("C:\\fakepath\\",'');
        const text=document.getElementById('commtext').value;
        window.location.href = 'commentPage.php?p='+pid+'&ci='+img+'&ct='+text;
    }

    function postComment(){
        const pid='<?=$pid?>';
        const img='<?=$commimg?>';
        const text=document.getElementById('commtext').value;
        window.location.href = 'commentPage.php?p='+pid+'&a=p'+'&ci='+img+'&ct='+text;
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
        <link rel="stylesheet" href="commentPageStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="PostStyleDark.css">
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="commentPageStyleDark.css">
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
    <div class="navbarButton" onclick="location.href='MainFeed.php'">
        Back to Feed
    </div>
</div>
</div>
<div class="popUpBox" style="display: <?php if ($popup=="") echo "none"; else echo "block"; ?>">
    <div class="popUpText">
        <?php echo $popup ?>
    </div>
</div>
<div class="middle">
    <div class="commentpost">
        <div class="namebar">
            <div class="postProfilePicFrame">
                <a href="profilePage.php?profile=<?php echo $postResult['Username'] ?>"><img class="postProfilePic" src="ProfileAndPostPics/<?php echo $posterPPic['ProfilePicture']?>" height="100%"></a>
            </div>
            <div class="postUsername">
                <a class="profileLink" href="profilePage.php?profile=<?php echo $postResult['Username'] ?>"><?php echo $postResult['Username']?></a>
            </div>
            <div class="postOptions">
                <img src="SiteSprites/options.png">
                <?php
                if ($postResult['Username']==$username){
                    ?>
                    <div class="postOptions-content">
                        <a href="?profile=<?php echo $postResult['Username'] ?>&de=<?php echo $row['Id']?>">Delete Post</a>
                    </div>
                <?php
                }
                else{
                ?>
                <div class="postOptions-content">
                    <a href="MainFeed.php?bu=<?php echo $postResult['Username']?>">Block User</a>
                    <a href="?p=<?php echo $pid?>&rp=<?php echo $pid?>">Report Post</a>
                    <a href="?p=<?php echo $pid?>&ru=<?php echo $postResult['Username']?>">Report User</a>
                    <?php
                    if ($_SESSION['admin']){
                        ?>
                        <a href="?profile=<?php echo $postResult['Username'] ?>&de=<?php echo $row['Id']?>">Delete Post</a>
                        <a href="AdminPage.php?r=p&b=<?php echo $postResult['Username']?>">Ban User</a>
                        <?php
                    }
                    ?>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="postDate">
                <?php echo $postResult['time']?>
            </div>
        </div>
        <div class="actualPost">
            <textarea readonly id="pcap" class="postCaption" cols="70" rows="1"><?php echo $postResult['Text']?></textarea>
            <script>
                auto_grow(document.getElementById('pcap'));
            </script>
            <?php
            if ($postResult['Image']!=null){
                ?>
                <div class="postImage">
                    <img src="ProfileAndPostPics/<?php echo $postResult['Image'] ?>">
                </div>
                <?php
            }
            ?>
        </div>
        <div class="likeBar">
            <?php
            if ($postlikeValue==0){ ?>
                <div class="likeButton">
                    <a href="?a=lp&p=<?php echo $pid?>&v=0"><img src="SiteSprites/like.png"></a>
                </div>
                <div class="likeButton">
                    <a href="?a=dp&p=<?php echo $pid?>&v=0"><img src="SiteSprites/dislike.png"></a>
                </div>
                <?php
            }
            else if ($postlikeValue==1){
                ?>
                <div class="likeButton">
                    <a href="?a=lp&p=<?php echo $pid?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                </div>
                <div class="likeButton">
                    <a href="?a=dp&p=<?php echo $pid?>&v=1"><img src="SiteSprites/dislike.png"></a>
                </div>
                <?php
            }
            else {
                ?>
                <div class="likeButton">
                    <a href="?a=lp&p=<?php echo $pid?>&v=-1"><img src="SiteSprites/like.png"></a>
                </div>
                <div class="likeButton">
                    <a href="?a=dp&p=<?php echo $pid?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
                </div>
                <?php
            }
            ?>
            <div class="likeNumber">
                <?php echo getPostLikes($pid,$mysqli) ?>
            </div>
            <div class="commentcomments">
                <?php
                $stmt = $mysqli->prepare("SELECT count(cid) as 'count' FROM comments where pid=?");
                $stmt->bind_param("i", $pid);
                $stmt->execute();
                $comments = $stmt->get_result();
                $stmt->close();
                $comments=$comments->fetch_assoc();
                if ($comments['count']==1) echo $comments['count']." Comment";
                else echo $comments['count']." Comments";
                ?>
            </div>
        </div>
        <div class="actualComments">
            <div class="writeComArea">
                <div class="commentPPicFrame">
                    <img class="commentPPic" src="ProfileAndPostPics/<?php echo $userPPic['ProfilePicture'] ?>">
                </div>
                <textarea class="writeCom" id="commtext" cols="70" rows="4" placeholder="Write a comment..."><?php echo $commtext?></textarea>
                <div class="commentLikeBar">
                    <button class="commentPostButton" onclick="postComment()">Post</button>
                    <div class="uploadimg">
                        <img class="uploadimgbutton" src="SiteSprites/image.png" height="160%" width="160%">
                        <form action="profilePage.php?profile=<?php echo $username ?>">
                            <input class="hiddenimg1" id="commimg" type="file" accept="image/*" onchange="uploadImage()">
                        </form>
                    </div>
                    <div id="uploadedfile" class="likeNumber">
                        <?php if ($commimg!="") echo $commimg ?>
                    </div>
                </div>
            </div>
            <?php
            $counter=0;
            foreach ($commentsResult as $row){
                $counter++;
                $stmt=$mysqli->prepare("SELECT ProfilePicture FROM profile where Username=?");
                $stmt->bind_param("s", $row['Username']);
                $stmt->execute();
                $commenterPic = $stmt->get_result()->fetch_assoc();
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
                                <a href="?p=<?php echo $pid?>&dc=<?php echo $row['cid']?>">Delete Comment</a>
                            </div>
                            <?php
                        }
                        else{
                        ?>
                            <div class="commentOptions-content">
                                <a href="?p=<?php echo $pid?>&bu=<?php echo $row['Username']?>">Block User</a>
                                <a href="?p=<?php echo $pid?>&rc=<?php echo $row['cid']?>">Report Comment</a>
                                <a href="?p=<?php echo $pid?>&ru=<?php echo $row['Username']?>">Report User</a>
                                <?php
                                if ($_SESSION['admin']){
                                    ?>
                                    <a href="AdminPage.php?r=p&b=<?php echo $row['Username']?>">Ban User</a>
                                    <?php
                                }
                                ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="commentDate">
                        <?php echo $row['time']?>
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
                                <a class="commentUsername" href="profilePage.php?profile=<?php echo $row['Username'] ?>"><?php echo $row['Username']?></a>
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
                                    <a href="?p=<?php echo $pid?>&a=lc&c=<?php echo $row['cid']?>&v=0"><img src="SiteSprites/like.png"></a>
                                </div>
                                <div class="commentLikeButton">
                                    <a href="?p=<?php echo $pid?>&a=dc&c=<?php echo $row['cid']?>&v=0"><img src="SiteSprites/dislike.png"></a>
                                </div>
                                <?php
                            }
                            else if ($likeValue==1){
                                ?>
                                <div class="commentLikeButton">
                                    <a href="?p=<?php echo $pid?>&a=lc&c=<?php echo $row['cid']?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                                </div>
                                <div class="commentLikeButton">
                                    <a href="?p=<?php echo $pid?>&a=dc&c=<?php echo $row['cid']?>&v=1"><img src="SiteSprites/dislike.png"></a>
                                </div>
                                <?php
                            }
                            else {
                                ?>
                                <div class="commentLikeButton">
                                    <a href="?p=<?php echo $pid?>&a=lc&c=<?php echo $row['cid']?>&v=-1"><img src="SiteSprites/like.png"></a>
                                </div>
                                <div class="commentLikeButton">
                                    <a href="?p=<?php echo $pid?>&a=dc&c=<?php echo $row['cid']?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="commentLikeNumber">
                                <?php echo getCommentLikes($row['cid'],$mysqli) ?>
                            </div>
                        </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
<?php
}
?>