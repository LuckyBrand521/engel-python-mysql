<?php
    session_start(['cookie_path'=>basename(__DIR__)]);
    $err = "";
    if(isset($_POST['login'])) {
        if($_POST['username'] == "engel" && $_POST['password'] == "Tc9B*^Bck*s@") {
            $_SESSION['login'] = true;
            header("Location: home.php");
		    die();
        }
        else {
            $err = "Wrong username or password!";
        }
    }
    else if(isset($_SESSION['login'])) {
        header("Location: home.php");
        die();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naturstein Crawler</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Roboto&display=swap");

        * {
            font-family: "Roboto", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        #container {
            display: flex; 
            justify-content: center; 
            align-items: center;
            height: 100vh;
        }
        #login_form {
            width: 400px;
            height: 400px;
            background: #b3b3b3;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            -webkit-box-shadow: 6px 7px 42px 0px rgba(0,0,0,0.75);
            -moz-box-shadow: 6px 7px 42px 0px rgba(0,0,0,0.75);
            box-shadow: 6px 7px 42px 0px rgba(0,0,0,0.75);
        }
        #login_form > div {
            margin-bottom: 15px;
        }
        .input {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        label {
            display: block;
            margin: 5px;
        }
        input[type='submit'] {
            width: 150px;
            height: 40px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            background: #4EBEFF;
            color: white;
        }
        input[type='text'], input[type='password'] {
            width: 200px;
            height: 40px;
            border-radius: 15px;
            padding: 5px;
            border: none;
        }
        input:focus, textarea:focus, select:focus{
            outline: none;
        }
    </style>
</head>
<body>
    <div id="container">
        <form id="login_form" action="" method="POST">
            <div class="input" style="color: red;"> 
                <?= $err; ?>
            </div>
            <div class="input"> 
                <h2>Naturstein Crawler</h2>
            </div>
            <div class="input">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="" required>
            </div>
            <div class="input">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="" required>
            </div>
            <div style="margin-top: 25px;"><input type="submit" name="login" value="Login"></div>
        </form>
    </div>
</body>
</html>