<?php
$mysqli = new mysqli("localhost", "root", "", "therock");

if ($mysqli->connect_error){
    die("Connection failed: ". $mysqli->connect_error);
}
session_start();
$username = $_SESSION['username'];
require_once 'Functions.php';
$darkmode=getDarkMode($username,$mysqli);

$popup="";
$refresh=isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
if (!$refresh) {
    if (isset($_GET['ad'])){
        sendFriendRequest($username,$_GET['ad'],$mysqli);
        $popup="Friend request sent.";
    }
    if (isset($_GET['ac'])){
        acceptFriendRequest($_GET['ac'],$username,$mysqli);
        $popup="Friend request accepted.";
    }
    if (isset($_GET['de'])){
        declineFriendRequest($_GET['de'],$username,$mysqli);
    }
    if (isset($_GET['b'])){
        blockUser($username,$_GET['b'],$mysqli);
        $blocked=$_GET['b'];
        $popup="$blocked was blocked";
    }
    if (isset($_GET['r'])){
        removeFriend($username,$_GET['r'],$mysqli);
        $popup="Friend was removed.";
    }
    if (isset($_GET['w'])){
        declineFriendRequest($username,$_GET['w'],$mysqli);
    }
}

$stmt = $mysqli->prepare("
SELECT Username,ProfilePicture FROM profile 
WHERE Username!=? and Username not in (
Select Username2 from friends
where Username1=?
union
SELECT Username1 from friends
where Username2=?
union
Select Username2 from friendrequests
where Username1=?
union
SELECT Username1 from friendrequests
where Username2=?
union
Select Username2 from blocked
where Username1=?
union
SELECT Username1 from blocked
where Username2=?
union
SELECT Username from banned
)");
$stmt->bind_param("sssssss", $username,$username,$username,$username,$username,$username,$username);
$stmt->execute();
$notFriendsResult = $stmt->get_result();
$stmt->close();

$stmt = $mysqli->prepare("
SELECT Username,ProfilePicture FROM profile
WHERE Username!=? and Username in (
Select Username2 from friends
where Username1=?
union
SELECT Username1 from friends
where Username2=?
)");
$stmt->bind_param("sss", $username,$username,$username);
$stmt->execute();
$friendsResult = $stmt->get_result();
$stmt->close();

$stmt=$mysqli->prepare("
SELECT Username,ProfilePicture FROM profile
WHERE Username!=? and Username in (
Select Username1 from friendrequests
where Username2=?
)");
$stmt->bind_param("ss", $username,$username);
$stmt->execute();
$incomingFriendRequestsResult = $stmt->get_result();
$stmt->close();
$stmt=$mysqli->prepare("
SELECT Username,ProfilePicture FROM profile
WHERE Username!=? and Username in (
Select Username2 from friendrequests
where Username1=?
)");
$stmt->bind_param("ss", $username,$username);
$stmt->execute();
$outgoingFriendRequestsResult = $stmt->get_result();
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
</script>

<html>
<head>
    <title>The Rock</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($darkmode==0){ ?>
        <link rel="stylesheet" href="NavbarStyleLight.css">
        <link rel="stylesheet" href="FriendStyleLight.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="FriendPageStyleLight.css">
    <?php } else { ?>
        <link rel="stylesheet" href="NavbarStyleDark.css">
        <link rel="stylesheet" href="FriendStyleDark.css">
        <link rel="stylesheet" href="popUp.css">
        <link rel="stylesheet" href="FriendPageStyleDark.css">
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
    <a class="active" href="friendPage.php">Friends</a>
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
</div>
<div class="popUpBox" style="display: <?php if ($popup=="") echo "none"; else echo "block"; ?>">
    <div class="popUpText">
        <?php echo $popup ?>
    </div>
</div>
<div class="container">
    <div class="left">
        <h1> Add Friends</h1>
        <div class="sub">
            Incoming friend requests
        </div>
        <?php
        foreach ($incomingFriendRequestsResult as $row){
            ?>
            <div class="friend">
                <div class="friendPicFrame">
                    <div class="friendProfilePic">
                        <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>"></a>
                    </div>
                </div>
                <div class="manageButtons">
                    <button class="twoButtons" onclick="document.location='friendPage.php?ac=<?php echo $row['Username']?>'">Accept</button>
                    <button class="twoButtons" onclick="document.location='friendPage.php?de=<?php echo $row['Username']?>'">Decline</button>
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
                    $stmt->bind_param("ssssssssss", $username,$row['Username'],$username,$row['Username']
                        ,$username,$row['Username'],$row['Username'],$username,$row['Username'],$username);
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
        <div class="sub">
            Make new friends
        </div>
        <?php
        foreach ($notFriendsResult as $row){
        ?>
        <div class="friend">
            <div class="friendPicFrame">
                <div class="friendProfilePic">
                    <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>"></a>
                </div>
            </div>
            <div class="manageButtons">
                <button class="twoButtons" onclick="document.location='friendPage.php?ad=<?php echo $row['Username']?>'">Add Friend</button>
                <button class="twoButtons" onclick="document.location='friendPage.php?b=<?php echo $row['Username']?>'">Block User</button>
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
                $stmt->bind_param("ssssssssss", $username,$row['Username'],$username,$row['Username']
                    ,$username,$row['Username'],$row['Username'],$username,$row['Username'],$username);
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
    <div class="right">
        <h1> Manage Friends</h1>
        <div class="sub">
            Friends
        </div>
        <?php
        foreach ($friendsResult as $row){
        ?>
        <div class="friend">
            <div class="friendPicFrame">
                <div class="friendProfilePic">
                    <img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>">
                </div>
            </div>
            <div class="manageButtons">
                <button class="twoButtons" onclick="document.location='friendPage.php?r=<?php echo $row['Username']?>'">Remove Friend</button>
                <button class="twoButtons" onclick="document.location='friendPage.php?b=<?php echo $row['Username']?>'">Block Friend</button>
            </div>
            <div class="friendUsername">
                <?php echo $row['Username']?>
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
        <div class="sub">
            Outgoing friend requests
        </div>
        <?php
        foreach ($outgoingFriendRequestsResult as $row){
            ?>
            <div class="friend">
                <div class="friendPicFrame">
                    <div class="friendProfilePic">
                        <a href="profilePage.php?profile=<?php echo $row['Username'] ?>"><img class="friendProfilePic" src="ProfileAndPostPics/<?php echo $row['ProfilePicture']?>"></a>
                    </div>
                </div>
                <div class="manageButtons">
                    <button class="withdrawButton" onclick="document.location='friendPage.php?w=<?php echo $row['Username']?>'">Withdraw Request</button>
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
                    $stmt->bind_param("ssssssssss", $username,$row['Username'],$username,$row['Username']
                        ,$username,$row['Username'],$row['Username'],$username,$row['Username'],$username);
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
</div>
</body>
</html>
