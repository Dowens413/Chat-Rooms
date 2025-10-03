<?php
session_start(); // start session need for every page that uses it 

// Loginging into the database

$servername = "localhost";
$username = "436_mysql_user";
$password = "123pwd456";
$dbname = "436db";


$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error)
    die("Connection failed: " . $mysqli->connect_error);   // connection check



$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';   // ajax 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    if ($action === "signup") {

        $newUsername = $_POST["username-signup"];
        $newPassword = $_POST["password-signup"];
        $newScreenName = $_POST["screenName-signup"];

        //Check if username or screenName already exists

        $checkStmt = $mysqli->prepare("Select * FROM users WHERE username = ? OR screenName = ?");       // checks the the database for the same username or screen name
        $checkStmt->bind_param("ss", $newUsername, $newScreenName);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            if ($isAjax) {
                echo json_encode(["success" => false, "message" => "Username or screen name already taken."]);
                exit;
            }
        } else {
            $insertStmt = $mysqli->prepare("INSERT INTO users(username,password,screenName) VALUES (?,?,?)");          // inserts the the user into the database mysql
            $insertStmt->bind_param("sss", $newUsername, $newPassword, $newScreenName);
            if ($insertStmt->execute()) {
                $_SESSION["username"] = $newUsername;
                $_SESSION["screenName"] = $newScreenName;

                if ($isAjax) {
                    echo json_encode(["success" => true]);
                    exit;
                } else {
                    header("Location: login.php");
                    exit;
                }
            } else {
                if ($isAjax) {
                    echo json_encode(["success" => false, "message" => "Error occurred during signup."]);
                    exit;
                }
            }

            $insertStmt->close();

        }
        $checkStmt->close();  //clsoing statements
    }

    if ($action === "login") {
        $username = $_POST["username-login"];      // when the login request is sent 
        $password = $_POST["password-login"];

        $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");          // search for the user name
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($user["password"] === $password) {
                $_SESSION["username"] = $user["username"];
                $_SESSION["screenName"] = $user["screenName"];

                if ($isAjax) {
                    echo json_encode(["success" => true]);       // if login is is successfull returns true in sucess and goesto the other page
                    exit;
                } else {

                    header("Location: login.php");
                    exit;
                }
            } else {
                if ($isAjax) {
                    echo json_encode(["success" => false, "message" => "Wrong password."]);
                    exit;
                }
            }
        } else {
            if ($isAjax) {
                echo json_encode(["success" => false, "message" => "Username not found."]);
                exit;
            }
        }

        $stmt->close();
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
        }


        .popup-overlay {
            display: none;
            /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* dimmed background */
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        #helpBox {
            cursor: pointer;
            user-select: none;
            /* prevents text selection on click */
            color: blue;

        }

        #helpBox:hover {
            color: darkblue;
        }


        .popup-box {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            max-width: 400px;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            font-family: sans-serif;
            text-align: center;
        }

        /* [x] Close button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }


        .signup-modal,
        .login-modal {
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            /* for the darkbackground */
            position: absolute;
            top: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            display: none;

        }

        .modal-content {
            width: 500px;
            height: 300px;
            background-color: white;
            border-radius: 4px;
            text-align: center;
            padding: 20px;
            position: absolute;
        }

        input {
            width: 300px;
            height: 40px;
            display: block;
            margin: 15px auto;
        }

        .close {
            position: absolute;
            top: 0;
            right: 14px;
            font-size: 42px;
            transform: rotate(45deg);
            cursor: pointer;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: dodgerblue;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: deepskyblue;
        }
    </style>
</head>

<body>
    <table style="width: 80%;">
        <tr>
            <th colspan="5"> Chat room via PHP web sockts</th>
        </tr>
        <tr>
            <td style="padding: 20px;">

            </td>
            <td>
                by Dujuan Owens

            </td>
            <td id="helpBox">
                Help
            </td>
            <td id="signup" style=" cursor:default;">
                Signup
            </td>
            <td id="login" style=" cursor:default;">
                login

            </td>

        </tr>



    </table>

    <div id="popupOverlay" class="popup-overlay"> <!-- the popup div for the overlay  -->
        <div class="popup-box">
            <span class="close-btn" onclick="hidePopup()">&times;</span>
            <div id="popupContent">This is the popup message</div>
        </div>
    </div>

    <div class="signup-modal">
        <div class="modal-content">
            <div class="close" onclick="hideForm(1)">+</div> <!-- closes  when click X  -->
            <h1 id="titlePopup">Sign up</h1>
            <form id="signupForm">
                <input name="username-signup" type="text" placeholder="username"> <!-- signup form  -->
                <input name="password-signup" type="password" placeholder="password">
                <input name="screenName-signup" type="text" placeholder="screenName">
                <button name="signupBtn" type="submit">Submit</button>
                <input type="hidden" name="action" value="signup" />
                <div id="signupError" style=color:red; margin-top: 10px;"></div>
                <!-- div for he error message like wrong input  -->
            </form>

        </div>

    </div> <!-- login form uses divs to hide the contents  -->
    <div class="login-modal">
        <div class="modal-content">
            <div class="close" onclick="hideForm(0)">+</div>
            <h1 id="titlePopup">Login</h1>
            <form id="loginForm">
                <input name="username-login" type="text" placeholder="username">
                <input name="password-login" type="password" placeholder="password">
                <button name="loginBtn" type="submit">Submit</button>
                <input type="hidden" name="action" value="login" />
                <div id="loginError" style="color:red; margin-top: 10px;"></div>
            </form>

        </div>

    </div>




    <script>


        function showPopup(message) {                                               //Popup function for the overlay    
            document.getElementById("popupContent").innerHTML = message;
            document.getElementById("popupOverlay").style.display = "flex";
        }

        function hidePopup() {
            document.getElementById("popupOverlay").style.display = "none";       //Hide popup function when  the x is click
        }

        const helpCell = document.getElementById("helpBox");
        helpCell.addEventListener("click", function (e) {
            e.preventDefault();      // help button  prompt
            showPopup(`Welcome to my chat room website! Here, you can connect with others in real-time using WebSockets.<br><br>

To get started, please login or signup for an account. If you're signing up, remember that your username and screen name must be unique. If they're already taken, you'll be prompted to choose something different. When the information is successfully entered, you'll be taken to the next page.<br><br>

Once logged in, you can join a single room at a time to chat with other users. Look for available rooms and click "join" to enter. Some rooms may require a key to be entered — you'll be prompted to enter a key in order to join that room.<br><br>

If there aren’t any rooms available, or you want to start a new one, simply click the (+) plus button to create a room. When creating a room, you'll need to enter a unique name. You also have the option to add a key for extra protection and to prevent unwanted access.<br><br>

After you've joined a room, the right side of your screen will display the current room and all the messages within it. Messages will clearly show the screen name of the user who sent them. Your own messages will be conveniently displayed as "me."<br><br>

Below the message display, you'll find a box where you can enter your message. Once you've typed your message, just click "send" to share it with everyone in the room.<br><br>

When you're ready to leave, simply click "logout" to end your session.<br><br>

Enjoy chatting!`);

        });

        const signupCell = document.getElementById("signup");    // makes the forms visible when pressed
        signupCell.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelector(".signup-modal").style.display = "flex";
        });

        const loginCell = document.getElementById("login");
        loginCell.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelector(".login-modal").style.display = "flex";        //on click the becomes visilbe

        });


        function hideForm(num) {     // hides the forms
            if (num == 1)
                document.querySelector(".signup-modal").style.display = "none";          // hide  login/siguno function
            else
                document.querySelector(".login-modal").style.display = "none";

        }


        document.addEventListener("DOMContentLoaded", function () {




            document.getElementById("signupForm").addEventListener("submit", function (e) {     // click lister for sigunpform and prevents page load
                e.preventDefault();
                const formData = new FormData(this);

                document.getElementById("signupError").innerText = "";        //clear error message div

                fetch("index.php", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" } // tell PHP this is AJAX
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "login.php"; //  redirect
                        } else {
                            document.getElementById("signupError").innerText = data.message;  //error message div text is added
                        }
                    });
            });

            document.getElementById("loginForm").addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(this);      // creats an instance for the form data

                fetch("index.php", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }         //header for the data
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {

                            window.location.href = "login.php"; // redirect to next page
                        } else {
                            document.getElementById("loginError").innerText = data.message;     //if there's an error dispay message

                        }
                    });
            });

        });




    </script>

</body>

</html>