<?php
session_start();
$servername = "localhost";
$username = "436_mysql_user";
$password = "123pwd456";
$dbname = "436db";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);
// else echo "Connected to db\n";


$data = json_decode(file_get_contents("php://input"), true);
$room = $data['roomName'];
$key = $data['key'] ?? null;

if (!isset($_SESSION['screenName'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);  //if screenName isn't set
    exit;
}

$name = $_SESSION['screenName'];  //gets name from the session 

//  Verify the chatroom and key
$stmt = $conn->prepare("SELECT chatroomKey FROM list_of_chatrooms WHERE chatroomName = ?");
$stmt->bind_param("s", $room);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode(["success" => false, "message" => "Room not found"]);
    exit;
}

if ($result['chatroomKey'] && $result['chatroomKey'] !== $key) {
    echo json_encode(["success" => false, "message" => "Incorrect key"]);    // sends back false and error massage
    exit;
}

//  Remove user from any other room
$deleteStmt = $conn->prepare("DELETE FROM chatroom WHERE screenName = ?");
$deleteStmt->bind_param("s", $name);
$deleteStmt->execute();

//  Add user to the new room
$insertStmt = $conn->prepare("INSERT INTO chatroom (screenName, chatroomName) VALUES (?, ?)");
$insertStmt->bind_param("ss", $name, $room);
$insertStmt->execute();

// . Update session with current room (optional)
$_SESSION['current_room'] = [
    "roomName" => $room,
    "key" => $result['chatroomKey']
];
echo json_encode(["success" => true]);
?>