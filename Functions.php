<?php
function likePost($likeValue,$postId,$username,$mysqli)
{
    try{
        if ($likeValue==0){
            $stmt = $mysqli->prepare("INSERT INTO likedposts values (?,?,1)");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
        else if ($likeValue==1){
            $stmt = $mysqli->prepare("DELETE FROM likedposts WHERE pid=? AND Username=?");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
        else if ($likeValue==-1){
            $stmt = $mysqli->prepare("UPDATE likedposts SET likeValue=1 WHERE pid=? and Username=?");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
    }
    catch (mysqli_sql_exception){

    }
}

function dislikePost($likeValue,$postId,$username,$mysqli)
{
    try{
        if ($likeValue==0){
            $stmt = $mysqli->prepare("INSERT INTO likedposts values (?,?,-1)");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
        else if ($likeValue==-1){
            $stmt = $mysqli->prepare("DELETE FROM likedposts WHERE pid=? AND Username=?");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
        else if ($likeValue==1){

            $stmt = $mysqli->prepare("UPDATE likedposts SET likeValue=-1 WHERE pid=? and Username=?");
            $stmt->bind_param("is", $postId,$username);
            $stmt->execute();
            $stmt->close();
        }
    }
    catch (mysqli_sql_exception){

    }
}

function blockUser($blocker,$blocked,$mysqli)
{
    try {
        $stmt = $mysqli->prepare("Select * from blocked where Username1=? and Username2=?");
        $stmt->bind_param("ss", $blocker, $blocked);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            removeFriend($blocker,$blocked,$mysqli);
            declineFriendRequest($blocker,$blocked,$mysqli);
            declineFriendRequest($blocked,$blocker,$mysqli);
            $stmt = $mysqli->prepare("Select Id from posts where Username=?");
            $stmt->bind_param("s", $blocker);
            $stmt->execute();
            $blockerPosts = $stmt->get_result();
            $stmt->close();
            foreach ($blockerPosts as $row) {
                $stmt = $mysqli->prepare("Select cid from comments where Username=? and pid=?");
                $stmt->bind_param("si", $blocked, $row["Id"]);
                $stmt->execute();
                $blockedComments = $stmt->get_result();
                $stmt->close();
                foreach ($blockedComments as $row2) {
                    deleteComment($row2['cid'],$mysqli);
                }
                $stmt = $mysqli->prepare("Select cid from comments where Username=? and pid=?");
                $stmt->bind_param("si", $blocker, $row["Id"]);
                $stmt->execute();
                $blockedComments = $stmt->get_result();
                $stmt->close();
                foreach ($blockedComments as $row2) {
                    $stmt = $mysqli->prepare("DELETE FROM likedcomments where cid=? and Username=?");
                    $stmt->bind_param("is", $row2['cid'], $blocked);
                    $stmt->execute();
                    $stmt->close();
                }
                $stmt = $mysqli->prepare("DELETE FROM likedposts where Username=? and pid=?");
                $stmt->bind_param("si", $blocked, $row['Id']);
                $stmt->execute();
                $stmt->close();
            }
            $stmt = $mysqli->prepare("Select * from posts where Username=?");
            $stmt->bind_param("s", $blocked);
            $stmt->execute();
            $blockedPosts = $stmt->get_result();
            $stmt->close();
            foreach ($blockedPosts as $row) {
                $stmt = $mysqli->prepare("Select cid from comments where Username=? and pid=?");
                $stmt->bind_param("si", $blocker, $row["Id"]);
                $stmt->execute();
                $blockerComments = $stmt->get_result();
                $stmt->close();
                foreach ($blockerComments as $row2) {
                    deleteComment($row2['cid'],$mysqli);
                }
                $stmt = $mysqli->prepare("Select cid from comments where Username=? and pid=?");
                $stmt->bind_param("si", $blocked, $row["Id"]);
                $stmt->execute();
                $blockedComments = $stmt->get_result();
                $stmt->close();
                foreach ($blockedComments as $row2) {
                    $stmt = $mysqli->prepare("DELETE FROM likedcomments where cid=? and Username=?");
                    $stmt->bind_param("is", $row2['cid'], $blocker);
                    $stmt->execute();
                    $stmt->close();
                }
                $stmt = $mysqli->prepare("DELETE FROM likedposts where Username=? and pid=?");
                $stmt->bind_param("si", $blocker, $row['Id']);
                $stmt->execute();
                $stmt->close();
                $stmt = $mysqli->prepare("DELETE FROM likedposts where Username=? and pid=?");
                $stmt->bind_param("si", $blocked, $row['Id']);
                $stmt->execute();
                $stmt->close();
            }
            $stmt = $mysqli->prepare("INSERT INTO blocked values (?,?)");
            $stmt->bind_param("ss", $blocker, $blocked);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){

    }
}

function unblockUser($blocker,$blocked,$mysqli){
        $stmt = $mysqli->prepare("DELETE FROM blocked WHERE Username1=? and Username2=?");
        $stmt->bind_param("ss",$blocker,$blocked);
        $stmt->execute();
        $stmt->close();
}

function reportUser($reporter,$reported,$mysqli){
    try {
        $stmt = $mysqli->prepare("Select * from reportedusers where reporter=? and reported=?");
        $stmt->bind_param("ss", $reporter, $reported);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("insert into reportedusers values (?,?)");
            $stmt->bind_param("ss", $reported, $reporter);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){
    }
}

function reportPost($reporter,$reported,$mysqli){
    try{
        $stmt = $mysqli->prepare("Select * from reportedposts where reporter=? and reported=?");
        $stmt->bind_param("si", $reporter, $reported);
        $stmt->execute();
        if ($stmt->get_result()->num_rows==0){
            $stmt->close();
            $stmt = $mysqli->prepare("insert into reportedposts values (?,?)");
            $stmt->bind_param("is",$reported,$reporter);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){
    }
}

function reportComment($reporter,$reported,$mysqli){
    try{
        $stmt = $mysqli->prepare("Select * from reportedcomments where reporter=? and reported=?");
        $stmt->bind_param("si", $reporter, $reported);
        $stmt->execute();
        if ($stmt->get_result()->num_rows==0){
            $stmt->close();
            $stmt = $mysqli->prepare("insert into reportedcomments values (?,?)");
            $stmt->bind_param("is",$reported,$reporter);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){

    }
}

function sendFriendRequest($sender, $receiver, $mysqli){
    try {
        $stmt = $mysqli->prepare("Select * from friendrequests where Username1=? and Username2=? union
                                Select * from friends where Username1=? and Username2=?");
        $stmt->bind_param("ssss", $sender, $receiver, $sender, $receiver);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("insert into friendrequests values (?,?)");
            $stmt->bind_param("ss", $sender, $receiver);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){

    }
}

function acceptFriendRequest($sender, $receiver, $mysqli){
    try{
        $stmt = $mysqli->prepare("Select * from friends where Username1=? and Username2=? union
                                Select * from friends where Username1=? and Username2=?");
        $stmt->bind_param("ssss", $sender, $receiver,$receiver, $sender);
        $stmt->execute();
        if ($stmt->get_result()->num_rows==0){
            $stmt->close();
            $stmt = $mysqli->prepare("insert into friends values (?,?)");
            $stmt->bind_param("ss",$sender,$receiver);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("delete from friendrequests where Username1=? and Username2=?");
            $stmt->bind_param("ss",$sender,$receiver);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){

    }
}

function declineFriendRequest($sender, $receiver, $mysqli){
    try{
        $stmt = $mysqli->prepare("Select * from friends where Username1=? and Username2=? union
                                Select * from friends where Username1=? and Username2=?");
        $stmt->bind_param("ssss", $sender, $receiver,$receiver, $sender);
        $stmt->execute();
        if ($stmt->get_result()->num_rows==0){
            $stmt->close();
            $stmt = $mysqli->prepare("delete from friendrequests where Username1=? and Username2=?");
            $stmt->bind_param("ss",$sender,$receiver);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception){

    }
}

function removeFriend($remover, $removed, $mysqli){
        $stmt = $mysqli->prepare("Select * from friends where Username1=? and Username2=? union
                                Select * from friends where Username1=? and Username2=?");
        $stmt->bind_param("ssss", $remover, $removed, $removed, $remover);
        $stmt->execute();
        if ($stmt->get_result()->num_rows != 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("delete from friends where Username1=? and Username2=?");
            $stmt->bind_param("ss", $remover, $removed);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("delete from friends where Username1=? and Username2=?");
            $stmt->bind_param("ss", $removed, $remover);
            $stmt->execute();
        }
        $stmt->close();
}

function changeProfilePic($username,$newpic,$mysqli){
    $stmt = $mysqli->prepare("Update profile set ProfilePicture=? where Username=?");
    $stmt->bind_param("ss",$newpic,$username);
    $stmt->execute();
    $stmt->close();
}

function changeBio($username, $newbio,$mysqli){
    $stmt = $mysqli->prepare("Update profile set Bio=? where Username=?");
    $stmt->bind_param("ss",$newbio,$username);
    $stmt->execute();
    $stmt->close();
}

function makePost($username,$caption,$image,$mysqli){
    $stmt = $mysqli->prepare("insert into posts (Username,Text,Image) values (?,?,?)");
    $stmt->bind_param("sss",$username,$caption,$image);
    $stmt->execute();
    $stmt->close();
}

function deletePost($pid,$mysqli){
    $stmt = $mysqli->prepare("Select cid from comments where pid=?");
    $stmt->bind_param("i",$pid);
    $stmt->execute();
    $comments=$stmt->get_result();
    $stmt->close();
    foreach($comments as $row){
        deleteComment($row['cid'],$mysqli);
    }
    $stmt = $mysqli->prepare("delete from likedposts where pid=?");
    $stmt->bind_param("i",$pid);
    $stmt->execute();
    $stmt->close();
    $stmt = $mysqli->prepare("delete from reportedposts where reported=?");
    $stmt->bind_param("i",$pid);
    $stmt->execute();
    $stmt->close();
    $stmt = $mysqli->prepare("delete from posts where Id=?");
    $stmt->bind_param("i",$pid);
    $stmt->execute();
    $stmt->close();
}

function getPostLikes($pid,$mysqli){
    $stmt = $mysqli->prepare("select count(*) as likes from likedposts where pid=? and likeValue=1");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $likes = $stmt->get_result();
    $likes=$likes->fetch_assoc();
    $stmt->close();
    $stmt = $mysqli->prepare("select count(*) as dislikes from likedposts where pid=? and likeValue=-1");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $dislikes = $stmt->get_result();
    $dislikes=$dislikes->fetch_assoc();
    $stmt->close();
    return $likes['likes']-$dislikes['dislikes'];
}

function getCommentLikes($cid,$mysqli){
    $stmt = $mysqli->prepare("select count(*) as likes from likedcomments where cid=? and likeValue=1");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $likes = $stmt->get_result();
    $likes=$likes->fetch_assoc();
    $stmt->close();
    $stmt = $mysqli->prepare("select count(*) as dislikes from likedcomments where cid=? and likeValue=-1");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $dislikes = $stmt->get_result();
    $dislikes=$dislikes->fetch_assoc();
    $stmt->close();
    return $likes['likes']-$dislikes['dislikes'];
}

function likeComment($likeValue,$cid,$username,$mysqli)
{
    if ($likeValue==0){
        $stmt = $mysqli->prepare("INSERT INTO likedcomments values (?,?,1)");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
    else if ($likeValue==1){
        $stmt = $mysqli->prepare("DELETE FROM likedcomments WHERE cid=? AND Username=?");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
    else if ($likeValue==-1){
        $stmt = $mysqli->prepare("UPDATE likedcomments SET likeValue=1 WHERE cid=? and Username=?");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
}

function dislikeComment($likeValue,$cid,$username,$mysqli)
{
    if ($likeValue==0){
        $stmt = $mysqli->prepare("INSERT INTO likedcomments values (?,?,-1)");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
    else if ($likeValue==-1){
        $stmt = $mysqli->prepare("DELETE FROM likedcomments WHERE cid=? AND Username=?");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
    else if ($likeValue==1){

        $stmt = $mysqli->prepare("UPDATE likedcomments SET likeValue=-1 WHERE cid=? and Username=?");
        $stmt->bind_param("is", $cid,$username);
        $stmt->execute();
        $stmt->close();
    }
}

function deleteComment($cid,$mysqli){
    $stmt = $mysqli->prepare("delete from likedcomments where cid=?");
    $stmt->bind_param("i",$cid);
    $stmt->execute();
    $stmt->close();
    $stmt = $mysqli->prepare("delete from reportedcomments where reported=?");
    $stmt->bind_param("i",$cid);
    $stmt->execute();
    $stmt->close();
    $stmt = $mysqli->prepare("delete from comments where cid=?");
    $stmt->bind_param("i",$cid);
    $stmt->execute();
    $stmt->close();
}

function makeComment($pid,$username,$comment,$image,$mysqli){
    $stmt = $mysqli->prepare("insert into comments (pid,Username,Text,Image) values (?,?,?,?)");
    $stmt->bind_param("isss",$pid,$username,$comment,$image);
    $stmt->execute();
    $stmt->close();
}

function getDarkMode($username,$mysqli) : int{
    $stmt = $mysqli->prepare("select darkmode from profile where Username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $darkmode=$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $darkmode['darkmode'];
}

function checkIfAdmin($username,$mysqli):bool{
    $stmt = $mysqli->prepare("select username from admins where username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows!=0) return true;
    return false;
}

function banUser($username,$reason,$mysqli){
    try {
        $stmt = $mysqli->prepare("Select * from banned where Username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM friends where Username1=? or Username2=?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM friendrequests where Username1=? or Username2=?");
            $stmt->bind_param("ss", $username,$username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM likedposts where Username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM likedcomments where Username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM reportedusers where reported=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("DELETE FROM blocked where Username1=? or Username2=?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("Select Id from posts where Username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $posts = $stmt->get_result();
            $stmt->close();
            foreach ($posts as $row) {
                $stmt = $mysqli->prepare("Select cid from comments where pid=?");
                $stmt->bind_param("i", $row["Id"]);
                $stmt->execute();
                $comments = $stmt->get_result();
                $stmt->close();
                foreach ($comments as $row2) {
                    deleteComment($row2['cid'],$mysqli);
                }
                deletePost($row['Id'],$mysqli);
            }
            $stmt = $mysqli->prepare("DELETE FROM comments where Username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO banned values (?,?)");
            $stmt->bind_param("ss", $username, $reason);
            $stmt->execute();
        }
        $stmt->close();
    }
    catch (mysqli_sql_exception $e){
        echo $e;
    }
}

function dismissUser($username,$mysqli){
    $stmt = $mysqli->prepare("DELETE FROM reportedusers where reported=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

function dismissPost($id,$mysqli){
    $stmt = $mysqli->prepare("DELETE FROM reportedposts where reported=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function dismissComment($id,$mysqli){
    $stmt = $mysqli->prepare("DELETE FROM reportedcomments where reported=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function getStrikes($username,$mysqli) : int{
    $stmt = $mysqli->prepare("Select strikes from profile where Username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $strikes=$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $strikes['strikes'];
}

function giveStrike($username,$mysqli): void
{
    $stmt = $mysqli->prepare("UPDATE profile set strikes=strikes+1 where Username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    if (getStrikes($username,$mysqli)==3) banUser($username,"3 instances of violating the Community Guidelines",$mysqli);
}

function isBanned($username,$mysqli) {
    $stmt = $mysqli->prepare("Select * from banned where Username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $banned=$stmt->get_result();
    $stmt->close();
    if ($banned->num_rows==0) return "good";
    else {
        $reason=$banned->fetch_assoc();
        return $reason['reason'];
    }
}