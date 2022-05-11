<?php
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
    <link rel="stylesheet" href="indexStyle.css">
</head>




<body>
<div class="bgbox">
    <div class="middle">
            <br>
            <h3>Welcome to</h3>
            <br>
            <img src="SiteSprites/TheRockLogoFinal.png" width=90%>
            <h4>The Official Dwayne "The Rock" Johnson Fan Page</h4>
            <form action="registerPage.php">
                <button type="submit"> Rockgister </button>
            </form>
            <br>

            <form action="loginPage.php">
                <div class="slighttext">
                    Already have an account?
                    <a href="loginPage.php">Johnsign in</a>
                </div>
            </form>
    </div>
</div>
</body>
</html>




