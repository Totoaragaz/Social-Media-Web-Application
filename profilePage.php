<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);

if (!isset($_GET['profile'])) echo 'Please stop messing with the URL :(';
else {

$profileUser=$_GET['profile'];

    $posttext="";
    $postimg="";
    $popup="";
    $refresh=isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
    if (!$refresh) {
        if (isset($_GET['a'])) {
            if ($_GET["a"] == "l") {
                if (isset($_GET['v']) and isset($_GET['p'])) {
                    likePost($_GET['v'], $_GET['p'], $username, $mysqli);
                }
            } else if ($_GET["a"] == "d") {
                if (isset($_GET['v']) and isset($_GET['p'])) {
                    dislikePost($_GET['v'], $_GET['p'], $username, $mysqli);
                }
            } else if ($_GET['a']=='p' and $username==$profileUser){
                if (isset($_GET['pi']) and isset($_GET['pt'])){
                    makePost($username,$_GET['pt'],$_GET['pi'],$mysqli);
                }
            }
        }
        if (isset($_GET['np']) and $profileUser==$username){
            changeProfilePic($username,$_GET['np'],$mysqli);
        }
        if (isset($_GET['nb']) and $profileUser==$username){
            changeBio($username,$_GET['nb'],$mysqli);
        }
        if (isset($_GET['d']) and $profileUser==$username){
            deletePost($_GET['d'],$mysqli);
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
        if (isset($_GET['e'])){
            if ($_GET['e']=='e'){
                $popup="Post can't be empty.";
            }
            if ($_GET['e']=='l'){
                $popup="Post can't be over 255 characters long.";
            }
        }
    }
    if (isset($_GET['pi']) and isset($_GET['pt']) and !isset($_GET['a'])){
        $posttext=$_GET['pt'];
        $postimg=$_GET['pi'];
    }

$stmt = $mysqli->prepare("SELECT Bio,ProfilePicture FROM profile WHERE username = ?");
$stmt->bind_param("s", $profileUser);
$stmt->execute();
$profileResult = $stmt->get_result();
$stmt->close();

if ($profileResult->num_rows==0) echo "Something went wrong";
else{
    $row=$profileResult->fetch_assoc();
    $profilePic = $row["ProfilePicture"];
    $bio=$row["Bio"];
}

$stmt = $mysqli->prepare("SELECT Id,Text,Image,time FROM posts WHERE username=? ORDER BY time desc");
$stmt->bind_param("s", $profileUser);
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

        window.addEventListener("beforeunload", () => {
            localStorage.setItem("scrollLeft",document.querySelector(".left").scrollTop);
            localStorage.setItem("scrollRight",document.querySelector(".right").scrollTop);
        })

        window.addEventListener("load",() => {
            document.querySelector(".left").scrollTop=localStorage.getItem("scrollLeft") || 0;
            document.querySelector(".right").scrollTop=localStorage.getItem("scrollRight") || 0;
        })

    function changeProfilePic(newpic){
        const user='<?=$username?>';
        const newnewpic=newpic.replace("C:\\fakepath\\",'');
        window.location.href = 'profilePage.php?profile='+user+'&np='+newnewpic;
    }

    function changeBio(){
        const user='<?=$username?>';
        const newbio=document.getElementById('nb').value;
        window.location.href = 'profilePage.php?profile='+user+'&nb='+newbio;
    }

    function uploadImage(){
        const user='<?=$username?>';
        const img=document.getElementById('postimg').value.replace("C:\\fakepath\\",'');
        const text=document.getElementById('posttext').value;
        if (text.length>255) window.location.href='profilePage.php?profile='+user+'$pi='+img;
        window.location.href = 'profilePage.php?profile='+user+'&pi='+img+'&pt='+text;
    }
    function post(){
        const user='<?=$username?>';
        const img='<?=$postimg?>';
        const text=document.getElementById('posttext').value;
        if (text==="") window.location.href='profilePage.php?profile='+user+'&e=e';
        else if (text.length>255) window.location.href = 'profilePage.php?profile='+user+'&e=l';
        else window.location.href = 'profilePage.php?profile='+user+'&a=p'+'&pi='+img+'&pt='+text;
    }

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
        <link rel="stylesheet" href="profilePageStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="PostStyleDark.css">
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="profilePageStyleDark.css">
    <?php } ?>
</head>

<body>
<div class="container">
<div class="navbar">
    <?php try {
        if (random_int(1, 10) == 7) {
            ?>
            <a href="MainFeed.php"><img src="SiteSprites/TheRockLogoFinalDwayne.png" height="100%" alt="The Rock"></a>
        <?php } else { ?>
            <a href="MainFeed.php"><img src="SiteSprites/TheRockLogoFinal.png" height=100% alt="The Rock"></a>
        <?php }
    } catch (Exception $e) {
    }
    if ($username==$profileUser){
        ?>
        <a class="active" href="profilePage.php?profile=<?php echo $username ?>">Profile</a>
    <?php
    }
    else {
        ?>
        <a class="notactive" href="profilePage.php?profile=<?php echo $username ?>">Profile</a>
    <?php
    }
    ?>
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
</div>
<br>
</div>
<div class="popUpBox" style="display: <?php if ($popup=="") echo "none"; else echo "block"; ?>">
    <div class="popUpText">
        <?php echo $popup ?>
    </div>
</div>

    <div class="left">
        <div class="profilebar">
            <br>
            <div class="profilepicframe">
                <img class=profilepic src="ProfileAndPostPics/<?php echo $profilePic?>" alt="<?php echo $profileUser ?>" width=100% height="100%">
                <?php
                if ($profileUser==$username){
                    ?>
                    <form action="profilePage.php?profile=<?php echo $username ?>">
                        <div class="changePic">
                            <input class="hidden ppic" id="profilepic" type="file" accept="image/*" onchange="changeProfilePic(this.value)")>
                            <label class="changePicText" for="profilepic">Change Profile Picture</label>
                        </div>
                    </form>
                <?php
                }
                ?>
            </div>
            <h1> <?php echo $profileUser?></h1>
            <?php
            if ($profileUser == $username){
                if (isset($_GET['cb'])){
                ?>
                    <div class="bioBox">
                        <textarea class="change" id="nb" name="nb" cols="40" rows="5" placeholder="Write a Bio..."><?php echo $bio?></textarea>
                        <button class="saveButton" id="saveB" onclick="document.location='profilePage.php?profile=<?php echo $username ?>&nb='+document.getElementById('nb').value">Save</button>
                        <button class="cancelButton" onclick="document.location='profilePage.php?profile=<?php echo $username ?>'">Cancel</button>
                    </div>


                    <?php
                }
                else{
                    ?>
                    <a href="profilePage.php?profile=<?php echo $username ?>&cb=1" style="text-decoration: none">
                        <textarea class="bioBox" placeholder="Click here to set a Bio!" cols="60" rows="7"><?php echo $bio ?></textarea>
                    </a>
                    <?php
                }
            }
            else{
                ?>
                <textarea class="bioBox" cols="60" rows="7"><?php echo $bio ?></textarea>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="right">
        <br>
        <?php
        if (blockeduser($profileUser,$username,$mysqli)) { ?>
            <div class="blockedText">
                <?php echo $profileUser ?> has blocked you.
            </div>
        <?php }
        else{
        if ($profileUser==$username){
            ?>
            <div class="post">
                <div class="namebar">
                    <div class="postProfilePicFrame">
                        <img class="postProfilePic" src="ProfileAndPostPics/<?php echo $profilePic?>" height="100%">
                    </div>
                    <div class="postUsername">
                        <?php echo $profileUser ?>
                    </div>
                </div>
                <div class="actualPost">
                    <textarea class="makePost" id="posttext" cols="80" rows="4" placeholder="Post Something!"><?php echo $posttext?></textarea>
                </div>
                <div class="likeBar">
                    <button class="postButton" onclick="post()">Post</button>
                    <div class="uploadimg">
                        <img class="uploadimgbutton" src="SiteSprites/image<?php if ($darkmode==1) echo "Dark"?>.png" height="140%" width="140%">
                        <form action="profilePage.php?profile=<?php echo $username ?>">
                            <input class="hiddenimg1" id="postimg" type="file" accept="image/*" onchange="uploadImage()">
                        </form>
                    </div>
                    <div id="uploadedfile" class="likeNumber">
                        <?php if ($postimg!="") echo $postimg ?>
                    </div>
                </div>
            </div>
        <?php
        }
        $counter=0;
        foreach ($postsResult as $row){
            $counter++;
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
                        <a href="profilePage.php?profile=<?php echo $profileUser ?>"><img class="postProfilePic" src="ProfileAndPostPics/<?php echo $profilePic?>" height="100%"></a>
                    </div>
                    <div class="postUsername">
                        <a class="profileLink" href="profilePage.php?profile=<?php echo $profileUser  ?>"><?php echo $profileUser ?></a>
                    </div>
                    <div class="postOptions">
                        <img src="SiteSprites/options<?php if ($darkmode==1) echo "Dark"?>.png">
                        <?php
                        if ($username!=$profileUser){
                        ?>
                            <div class="postOptions-content">
                                <a href="?profile=<?php echo $profileUser ?>&bu=<?php echo $profileUser?>">Block User</a>
                                <a href="?profile=<?php echo $profileUser ?>&rp=<?php echo $row['Id']?>">Report Post</a>
                                <a href="?profile=<?php echo $profileUser ?>&ru=<?php echo $profileUser?>">Report User</a>
                                <?php
                                if ($_SESSION['admin']){
                                    ?>
                                    <a href="AdminPage.php?r=p&b=<?php echo $profileUser?>">Ban User</a>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        else{
                            ?>
                            <div class="postOptions-content">
                                <a href="?profile=<?php echo $profileUser ?>&d=<?php echo $row['Id']?>">Delete Post</a>
                            </div>
                            <?php
                        }
                        ?>
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
                    if ($row['Image']!=null){
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
                                <a href="?profile=<?php echo $profileUser?>&a=l&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/like<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?profile=<?php echo $profileUser?>&a=d&p=<?php echo $row['Id']?>&v=0"><img src="SiteSprites/dislike<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <?php
                        }
                        else if ($likeValue==1){
                            ?>
                            <div class="likeButton">
                                <a href="?profile=<?php echo $profileUser?>&a=l&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/like_pressed.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?profile=<?php echo $profileUser?>&a=d&p=<?php echo $row['Id']?>&v=1"><img src="SiteSprites/dislike<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <?php
                        }
                        else {
                            ?>
                            <div class="likeButton">
                                <a href="?profile=<?php echo $profileUser?>&a=l&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/like<?php if ($darkmode==1) echo "Dark"?>.png"></a>
                            </div>
                            <div class="likeButton">
                                <a href="?profile=<?php echo $profileUser?>&a=d&p=<?php echo $row['Id']?>&v=-1"><img src="SiteSprites/dislike_pressed.png"></a>
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
        ?>
    </div>
</div>
</body>
</html>
<?php
}
?>