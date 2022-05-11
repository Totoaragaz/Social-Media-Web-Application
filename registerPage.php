<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "therock");
if (isset($_POST['username'])) {

    // verificam daca s-au completat formurile, cu un default value daca nu au fost completate.
    // posibil aici sa facem si o validare in care verificam daca putem folosii datele de la user.
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";
    if (empty($username)) {
        header("Location:registerPage.php?error=Username is required.");
        exit();
    }
    else if (str_contains($username,"<script>")){
        header("Location:registerPage.php?error=Fuck you.");
        exit();
    }
    else if (!(str_contains(strtolower($username), 'dwayne') or str_contains(strtolower($username), 'rock') or str_contains(strtolower($username), 'johnson'))) {
        header("Location:registerPage.php?error=Username must contain 'Dwayne','Rock' or 'Johnson'.");
        exit();
    }
    if (empty($password)) {
        header("Location:registerPage.php?error=Password is required.");
        exit();
    }
    else {
        $hashedPassword = md5($password);

        // cauta daca exista un rand cu id-ul respectiv
        $stmt = $mysqli->prepare("SELECT * FROM profile WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows == 0) {
                // daca nu exista => create
            $createStmt = $mysqli->prepare("INSERT INTO profile (username,password) VALUES (?, ?)");
            $createStmt->bind_param("ss", $username, $hashedPassword);
            $createStmt->execute();
            $createStmt->close();
            $_SESSION["username"]=$username;
            $_SESSION["friendsonly"]=0;
            $_SESSION['admin']=false;
            header("Location:profilePage.php?profile=".$username);
            exit();
        }
        else {
            header("Location:registerPage.php?error=User already exists.");
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
    <link rel="stylesheet" href="registerStyle1.css">
</head>

<body>
<div class="bgbox">
    <div class="middle">
        <div class="header">
            <a href="index.php"><img src="SiteSprites/TheRockLogoFinal.png" width=50% alt="The Rock"></a>
        </div>
        <h1>Rockgister User</h1>
        <div class="form">
            <form method="post" action="registerPage.php">
                <div>
                    <label>
                        Username:
                        <input type="text" name="username" value=""/>
                    </label>
                </div>
                <div class="smoltext">
                    <label>
                        Username must contain 'Dwayne','Rock' or 'Johnson' (case not sensitive)
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
                <button type="submit">Register</button>
            </form>
        </div>
        <br>
        <form action="registerPage.php">
            <div class="slighttext">
                Already have an account?
                <a href="loginPage.php">Johnsign in</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
