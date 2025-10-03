<?php
session_start();



if(isset($_POST["logout"]))
{


if(isset($_SESSION["current_room"]))     // removes user from the database that keeps track of the user
{
   
    $screenName = $_SESSION['screenName'];
    $mysqli = new mysqli("localhost", "436_mysql_user", "123pwd456", "436db");

     if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }


    $stmt = $mysqli->prepare("DELETE FROM chatroom WHERE screenName = ?");
    $stmt->bind_param("s",  $screenName);
    $stmt->execute();
    $stmt->close();

    $mysqli->close();


}


    session_unset();
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
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse; /* Optional: makes borders collapse into a single border */
          }
          th, td {
            padding: 8px; /* Optional: adds space inside cells */
          }
        .createChat-modal{
            width:100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            position:absolute ;
            top: 0;
            display:flex;
            justify-content: center;
           align-items: center;
           display: none;  
           
        }
        .modal-content{
            width:500px;
            height: 300px;
            background-color:white;
            border-radius:4px;
            text-align: center;
            padding: 20px;
            position:absolute;
        }

        .close{
            position: absolute;
            top:0;
            right:14px;
            font-size:42px;
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
             .chat-input{
            width: 300px;
            height:40px;
            display:block;
            margin:15px auto;
        }

        #messageCell {
         height: 125px;
}
  
    </style>
</head>
<body>
    <table id="myTable" style = "width: 100%;">
        <tr>
            <th colspan="9"> Chat room via PHP web sockts</th>
        </tr>
        <tr>
           <td style = "padding: 20px;">

           </td>
           <td colspan ="5">
            by Dujuan Owens

           </td>
         <td id="helpBox">
            Help
         </td>
        
           <td id = "Logout"> 
            <form method="POST" action="login.php">
                <button type="submit" name="logout">Logout</button>
            </form>

           </td>
           
        </tr>
        <tr>
            <td colspan="9"></td>
        </tr>
        <tr>
            <td colspan = "3">Avaiable Rooms <span id = "moreRooms" style = " cursor: pointer; font-weight:bold;">+</span></td>
            <td  id ="vertSpan" colspan="3" rowspan="6"></td>
            <td id = "currentRoomName" rowspan="2" colspan = "3"> Current room name</td>
        </tr>
        <tr >
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

        <td colspan="3" rowspan="4"  style="vertical-align: top;">
        <div id="roomsList" style="display: block; max-height: 150px; overflow-y: auto; border: 1px solid #ccc; margin-top: 5px; padding: 5px;">
            <table style = " width: 100%;">
                <tbody id = "room-info">
                


            </tbody>
            </table>
        </div>
        </td>
        <td id = "messageCell"colspan="3">
        <div id="messageList" style="display: block; max-height: 300px; overflow-y: auto; border: 1px solid #ccc; margin-top: 5px; padding: 5px;">
            <table  id = "messageTable" style = " width: 100%;">
                
            </table>

        </div>
        
        
        </td>
        <tr>
            <td colspan="3">
               
            </td>
        </tr>
        <tr>
            <td colspan ="3">
                <form style="width: 100%; margin: 0; padding: 0;">
                    <input id = "msgInput" type="text" placeholder="Type new message here" style="width: 95%; margin: 0;">

                </form>
                
            </td>
        </tr>
        <tr>
            <td  colspan ="3">
                <button  id ="msgBtn" style=" width:100%; margin: 0; padding: 0; color:white;">Send-button</button>  
            </td>
            
        </tr>

    </table>

        </script>

    <div class = "createChat-modal">
        <div class = "modal-content">
            <div class ="close" onclick = "closeform()">+</div>
            <h1 id = "titlePopup">Create a ChatRoom</h1>
            <form id="createChatForm">
                <input class="chat-input" name="chatroomName" type="text" placeholder="name">
                <input class="chat-input" name="key" type="text" placeholder="Key">
                 <button name="createChatBtn" type="submit">Submit</button>
            </form>
            <div id="chatroom-message" style="color: red; margin-top: 10px;"></div>
        
    </div>
      
</div>

    <script>
let socket = null;
let currentUsername = "<?php echo $_SESSION['screenName']?>"; // optional: depends on your login system
let currentRoom = <?php echo json_encode($_SESSION['current_room'] ?? null); ?>;



    function showPopup(message) {                                               //Popup function for the overlay    
        document.getElementById("popupContent").innerHTML = message;
        document.getElementById("popupOverlay").style.display = "flex";
    }

    function hidePopup() {
        document.getElementById("popupOverlay").style.display = "none";       //Hide popup function when  the x is click
    }

    const helpCell = document.getElementById("helpBox");
    helpCell.addEventListener("click",function(e)

    {
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

 document.addEventListener("DOMContentLoaded", function() {
    const formCell = document.getElementById("moreRooms");
    formCell.addEventListener("click", function(e) {
        e.preventDefault();
        openForm();
    });


    document.getElementById("createChatForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const msgBox = document.getElementById("chatroom-message");
        msgBox.innerText= "";

        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            //const msgBox = document.getElementById("chatroom-message");
            msgBox.style.color = data.success ? "green" : "red";
            msgBox.innerText = data.message;

            if(data.success)
        {
            socket.send(JSON.stringify({
            type: "notifyRoomStatus",
            chatroomName: data.recentRoom;
        }));
    







            
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
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>${room.chatroomName}</td>
                    <td>${room.chatroomKey ? " 🔒 Locked": "🔓 Public"} </td>
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
        key = prompt("This room is locked. Enter the key:");
        if (key === null) return;
    }

    fetch("join_room.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
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
   socket = new WebSocket("ws://3.147.99.178:8080");


    socket.onopen = () => {
        // Send join message
        socket.send(JSON.stringify({
            type: "join",
            username: currentUsername,
            chatroomName: currentRoom.roomName,
            key: currentRoom.key
        }));
    };

    socket.onmessage = (event) => {
        console.log("Raw message received:", event.data);
        const data = JSON.parse(event.data);
        if (data.type === "message") {
             const sender = data.from === currentUsername ? "me" : data.from;
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
    socket.onroom = function(event) {
    const data = JSON.parse(event.data);
    if (data.type === "roomStatus") {
        console.log(`Room: ${data.chatroomName}, Status: ${data.status}`);
        // Update your UI to display this information
        if (data.status === "locked") {
            // Show a lock icon or "Locked" text next to the room name
        } else if (data.status === "unlocked") {
            // Show an unlock icon or "Unlocked" text
        } else {
            // Room not found or other status
        }
    }
    // ... handle other message types (e.g., "message", "joined", "error")
};

    socket.onclose = () => {
        console.log("Socket closed");
    };

    socket.onerror = (err) => {
        console.error("Socket error:", err);
    };
}




document.getElementById("msgBtn").addEventListener("click", function(e) {
    e.preventDefault();
    const input = document.getElementById("msgInput");
    const text = input.value.trim();
    if (text === "" || !socket || socket.readyState !== WebSocket.OPEN) return;

    socket.send(JSON.stringify({
        type: "message",
        text: text
    }));

    input.value = ""; // Clear input
});


setInterval(fetchChatrooms, 5000);
fetchChatrooms();
    </script>

 
</body>
</html>