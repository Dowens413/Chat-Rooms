<?php
session_start();  // start session



if (isset($_POST["logout"])) {


    if (isset($_SESSION["current_room"]))     // removes user from the database that keeps track of the user
    {

        $screenName = $_SESSION['screenName'];
        $mysqli = new mysqli("localhost", "436_mysql_user", "123pwd456", "436db");

        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }


        $stmt = $mysqli->prepare("DELETE FROM chatroom WHERE screenName = ?");
        $stmt->bind_param("s", $screenName);
        $stmt->execute();
        $stmt->close();

        $mysqli->close();


    }


    session_unset();      // ends session then redire to start page
    session_destroy();
    header("Location: index.php");
    exit();
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

        .createChat-modal {
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
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

        .close {
            position: absolute;
            top: 0;
            right: 14px;
            font-size: 42px;
            transform: rotate(45deg);    /* rotate the +  so it can look like an x  */
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

        .chat-input {
            width: 300px;
            height: 40px;
            display: block;
            margin: 15px auto;
        }

        #messageCell {
            height: 125px;
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

    </style>
</head>

<body>
    <table id="myTable" style="width: 100%;">
        <tr>
            <th colspan="9"> Chat room via PHP web sockts</th>
        </tr>
        <tr>
            <td style="padding: 20px;">

            </td>
            <td colspan="5">
                by Dujuan Owens

            </td>
            <td id="helpBox">
                Help
            </td>

            <td id="Logout">
                <form method="POST" action="login.php">
                    <button type="submit" name="logout">Logout</button>
                </form>

            </td>

        </tr>
        <tr>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan="3">Avaiable Rooms <span id="moreRooms" style=" cursor: pointer; font-weight:bold;">+</span>
            </td>
            <td id="vertSpan" colspan="3" rowspan="6"></td>
            <td id="currentRoomName" rowspan="2" colspan="3"> Current room name</td>
        </tr>
        <tr>
            <td>
                Room Name
            </td>
            <td>
                status
            </td>
            <td>
                Join
            </td>
        </tr>

        <td colspan="3" rowspan="4" style="vertical-align: top;">
            <div id="roomsList"
                style="display: block; max-height: 150px; overflow-y: auto; border: 1px solid #ccc; margin-top: 5px; padding: 5px;">
                <table style=" width: 100%;">
                    <tbody id="room-info">



                    </tbody>
                </table>
            </div>
        </td>
        <td id="messageCell" colspan="3">
            <div id="messageList"
                style="display: block; max-height: 300px; overflow-y: auto; border: 1px solid #ccc; margin-top: 5px; padding: 5px;">
                <table id="messageTable" style=" width: 100%;">

                </table>

            </div>


        </td>
        <tr>
            <td colspan="3">

            </td>
        </tr>
        <tr>
            <td colspan="3">
                <form style="width: 100%; margin: 0; padding: 0;">
                    <input id="msgInput" type="text" placeholder="Type new message here" style="width: 95%; margin: 0;">

                </form>

            </td>
        </tr>
        <tr>
            <td colspan="3">
                <button id="msgBtn" style=" width:100%; margin: 0; padding: 0; color:white;">Send-button</button>
            </td>

        </tr>

    </table>

   <div id="popupOverlay" class="popup-overlay"> <!-- the popup div for the overlay  -->
        <div class="popup-box">
            <span class="close-btn" onclick="hidePopup()">&times;</span>
            <div id="popupContent">This is the popup message</div>
        </div>
    </div>

    <div class="createChat-modal">
        <div class="modal-content">
            <div class="close" onclick="closeform()">+</div>
            <h1 id="titlePopup">Create a ChatRoom</h1>
            <form id="createChatForm" method="POST" action="create_chatroom.php">
                <input class="chat-input" name="chatroomName" type="text" placeholder="name">
                <input class="chat-input" name="key" type="text" placeholder="Key">
                <button name="createChatBtn" type="submit">Submit</button>
            </form>
            <div id="chatroom-message" style="color: red; margin-top: 10px;"></div>

        </div>

    </div>

    <script>
        let socket = null;
        let currentUsername = "<?php echo $_SESSION['screenName'] ?>";
        let currentRoom = <?php echo json_encode($_SESSION['current_room'] ?? null); ?>;



        function showPopup(message) {                                               //Popup function for the overlay    
            document.getElementById("popupContent").innerHTML = message;
            document.getElementById("popupOverlay").style.display = "flex";
        }

        function hidePopup() {
            document.getElementById("popupOverlay").style.display = "none";       //Hide popup function when  the x is click
        }

        const helpCell = document.getElementById("helpBox");
        helpCell.addEventListener("click", function (e) {
            e.preventDefault();
            showPopup(`Welcome to my chat room website! Here, you can connect with others in real-time using WebSockets.<br><br>

To get started, please login or signup for an account. If you're signing up, remember that your username and screen name must be unique. If they're already taken, you'll be prompted to choose something different. When the information is successfully entered, you'll be taken to the next page.<br><br>

Once logged in, you can join a single room at a time to chat with other users. Look for available rooms and click "join" to enter. Some rooms may require a key to be entered — you'll be prompted to enter a key in order to join that room.<br><br>

If there aren’t any rooms available, or you want to start a new one, simply click the (+) plus button to create a room. When creating a room, you'll need to enter a unique name. You also have the option to add a key for extra protection and to prevent unwanted access.<br><br>

After you've joined a room, the right side of your screen will display the current room and all the messages within it. Messages will clearly show the screen name of the user who sent them. Your own messages will be conveniently displayed as "me."<br><br>

Below the message display, you'll find a box where you can enter your message. Once you've typed your message, just click "send" to share it with everyone in the room.<br><br>

When you're ready to leave, simply click "logout" to end your session.<br><br>

Enjoy chatting!`);

        });

        document.addEventListener("DOMContentLoaded", function () {
            const formCell = document.getElementById("moreRooms");     // when page is loaed do this.. #ccc
            formCell.addEventListener("click", function (e) {
                e.preventDefault();
                openForm();


            });
            setupWebSocket(); // set ups  websocket on page load


            document.getElementById("createChatForm").addEventListener("submit", function (e) {     //when submit is pressed and prevent reload of the  page do this ..
                e.preventDefault();

                const msgBox = document.getElementById("chatroom-message");   // get the chatroom message the div that dispays output for the chatroom form
                msgBox.innerText = "";

                const form = e.target;
                const formData = new FormData(form);       // get the data from the form

                // 🔍 Log all form values


                fetch(form.action, {               // send/recieve the data via post 
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        msgBox.style.color = data.success ? "green" : "red";
                        msgBox.innerText = data.message;
                        document.getElementsByClassName("chat-input")[0].value = "";
                        document.getElementsByClassName("chat-input")[1].value = "";    // clears the input boxes for better UI  

                        if (data.success)  // success is true  do

                        {
                            if (socket && socket.readyState === WebSocket.OPEN) {   //only when the socket is open

                                socket.send(JSON.stringify({    // seends json 
                                    type: "room",
                                    recentRoom: data.recentRoom,
                                    status: data.status,

                                }));
                            } else
                                console.warn("WebSocket not open when trying to broadcast room");
                        }
                    })
                    .catch(err => {
                        console.error("Fetch error", err);
                    });
            });

        });


        function openForm() {
            document.getElementsByClassName("createChat-modal")[0].style.display = "flex";
        }

        function closeform() {
            document.getElementsByClassName("createChat-modal")[0].style.display = "none";
        }

        function fetchChatrooms() {
            fetch('fetch_chatrooms.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('room-info');
                    tbody.innerHTML = ''; // Clear old rows
                    data.forEach(room => {
                        const row = document.createElement('tr');    // creats a row for each room retrieved

                        row.innerHTML = `
                    <td>${room.chatroomName}</td>
                    <td>${room.chatroomKey ? " 🔒 Locked" : "🔓 Public"} </td>
                    <td><button onclick="joinRoom('${room.chatroomName}','${room.chatroomKey}')">Join</button></td>  
                `;

                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading chatrooms:', error);
                });
        }



        function joinRoom(roomName, roomKey) {
            let key = "";

            if (roomKey !== "null" && roomKey !== "") {
                key = prompt("This room is locked. Enter the key:");   // if locked promt for a key   
                if (key === null) return;
            }

            fetch("join_room.php", {    // fetch to  join_room.php
                method: "POST",
                headers: { "Content-Type": "application/json" },     // send json as stated in the header
                body: JSON.stringify({ roomName, key })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("currentRoomName").innerText = roomName;
                        document.getElementById("messageTable").innerHTML = "";

                        currentRoom = { roomName, key };

                        // Close old socket if switching rooms
                        if (socket) socket.close();

                        setupWebSocket(); // start connection
                    } else {
                        alert(data.message);
                    }
                });
        }


        function setupWebSocket() {
            if (socket && socket.readyState === WebSocket.OPEN) return;
            socket = new WebSocket("ws://3.147.99.178:8080");


            socket.onopen = () => {
                // Send join message
                if (currentRoom) {
                    socket.send(JSON.stringify({              //if there's a current roon join it 
                        type: "join",
                        username: currentUsername,
                        chatroomName: currentRoom.roomName,
                        key: currentRoom.key
                    }));
                }
            };



            socket.onmessage = (event) => {
                //console.log("Raw message received:", event.data);
                const data = JSON.parse(event.data);


                if (data.type === "room") {
                    console.log("room");
                    const messageTable = document.getElementById("messageTable");
                    const row = document.createElement("tr");
                    row.innerHTML = `<td>New ROOM<strong>${data.room}</strong> Status: ${data.status}</td>`;
                    messageTable.appendChild(row);
                    // Scroll to bottom
                    messageTable.parentElement.scrollTop = messageTable.parentElement.scrollHeight;
                }
                else if (data.type === "message") {
                    const sender = data.from === currentUsername ? "me" : data.from;   // if the users screenNmae match they will see "ME' else recipent's screenName
                    const messageTable = document.getElementById("messageTable");
                    const row = document.createElement("tr");
                    row.innerHTML = `<td><strong>${sender}</strong>: ${data.text}</td>`;
                    messageTable.appendChild(row);
                    // Scroll to bottom
                    messageTable.parentElement.scrollTop = messageTable.parentElement.scrollHeight;
                } else if (data.type === "error") {
                    alert(data.message);
                }
            };

            socket.onclose = () => {
                console.log("Socket closed");
            };

            socket.onerror = (err) => {
                console.error("Socket error:", err);
            };
        }


        document.getElementById("msgBtn").addEventListener("click", function (e) {    // click the meesage button do this 
            e.preventDefault();
            const input = document.getElementById("msgInput");
            const text = input.value.trim();
            if (text === "" || !socket || socket.readyState !== WebSocket.OPEN) return;

            socket.send(JSON.stringify({
                type: "message",
                text: text
            }));

            input.value = ""; // Clears input after click 
        });


        setInterval(fetchChatrooms, 5000);   // reloads the chat room so they stay up today ever 5 seconds
        fetchChatrooms(); 
    </script>


</body>

</html>