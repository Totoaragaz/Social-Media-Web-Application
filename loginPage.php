<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "therock");
$page="db_login";

require_once 'Functions.php';

if (isset($_POST['username'])) {

    // verificam daca s-au completat formurile, cu un default value daca nu au fost completate.
    // posibil aici sa facem si o validare in care verificam daca putem folosii datele de la user.
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";

    if (empty($username)){
        header("Location:loginPage.php?error=Username is required.");
        exit();
    }
    else if (str_contains($username,"<script>")){
        header("Location:loginPage.php?error=Fuck you.");
        exit();
    }
    else if (empty($password)){
        header("Location:loginPage.php?error=Password is required.");
        exit();
    }else{

        $hashedPassword = md5($password);

        // cauta daca exista un rand cu id-ul respectiv
        $stmt = $mysqli->prepare("SELECT * FROM profile WHERE username = ? and password = ?");
        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows != 0){
            $reason=isBanned($username,$mysqli);
            if ($reason!="good") header("Location:loginPage.php?error=User was banned for $reason");
            else {
                //LOG IN
                $_SESSION["username"]=$username;
                $_SESSION["friendsonly"]=0;
                if (checkIfAdmin($username,$mysqli)) $_SESSION['admin']=true;
                else $_SESSION['admin']=false;
                header("Location:profilePage.php?profile=".$username);
                exit();
            }
        }
        else {
            header("Location:loginPage.php?error=Incorrect username or password.");
            exit();
        }
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
        <link rel="stylesheet" href="loginStyle1.css">
    </head>




    <body>
    <div class="bgbox">
        <div class="middle">
            <div class="header">
            <a href="index.php"><img src="SiteSprites/TheRockLogoFinal.png" width=50% alt="The Rock"></a>
            </div>
        <h1>Johnsign In</h1>
        <div class="form">
            <form method="post" action="loginPage.php">
                <div>
                    <label>
                        Username:
                        <input type="text" name="username" value=""/>
                    </label>
                </div>
                <div>
                    <label>
                        Password:
                        <input type="password" name="password" value=""/>
                    </label>
                </div>
                <?php if (isset($_GET['error'])) { ?>

                    <p class="error"><?php echo $_GET['error']; ?></p>

                <?php } ?>
                <br>
                <button type="submit">Rock in</button>
            </form>
        </div>
        <br>
        <form action="loginPage.php">
            <div class="slighttext">
                Don't have an account?
                <a href="registerPage.php">Rockgister here</a>
            </div>
        </form>
        </div>
        </div>
        </body>
        </html>

<?php

?>