<?php


header("Access-Control-Allow-Origin: http://3.147.178");

// Allow requests from ip 

$servername = "localhost";
$user = "436_mysql_user";
$pass = "123pwd456";
$dbname = "436db";


$conn = new mysqli($localhost, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT chatroomName, chatroomKey FROM list_of_chatrooms";    //returns the data from the list_of_chatrooms
$result = $conn->query($sql);

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rooms);
?>