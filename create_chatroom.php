<?php



header('Content-Type: application/json');
ob_clean();// cleaing output buffer




$servername = "localhost";
$username = "436_mysql_user";
$password = "123pwd456";               //values for logining intp mysql
$dbname = "436db";

$mysqli = new mysqli($servername, $username, $password, $dbname);  //conncecting

if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);  //testing connection
    exit();
}

file_put_contents("php://stderr", print_r($_POST, true));  // logs POST data to server error log

$chatroomName = trim($_POST["chatroomName"] ?? "");
$key = trim($_POST["key"] ?? "");
$status = "";

if ($chatroomName === "") {
    echo json_encode(["success" => false, "message" => "Chatroom name is required."]);  //null exit 
    exit();
}

// Check if chatroom already exists
$stmt = $mysqli->prepare("SELECT chatroomName FROM list_of_chatrooms WHERE chatroomName = ?");
$stmt->bind_param("s", $chatroomName); //bind to together with variable 
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {               //if there are any commun chatroom it will return  number higher than zero
    echo json_encode(["success" => false, "message" => "Chatroom name already taken."]);
} else {
    $stmt->close();

    // Prepare the insert with optional NULL
    if ($key === "") {
        $stmt = $mysqli->prepare("INSERT INTO list_of_chatrooms (chatroomName, chatroomKey) VALUES (?, NULL)");    // no key insert into database like this
        $stmt->bind_param("s", $chatroomName);
        $status = "unlocked";

    } else {
        $stmt = $mysqli->prepare("INSERT INTO list_of_chatrooms (chatroomName, chatroomKey) VALUES (?, ?)");     //if there's a key 
        $stmt->bind_param("ss", $chatroomName, $key);   // inserts variables  into statement
        $status = "locked";
    }

    if ($stmt->execute()) {     //if exceuted
        echo json_encode([
            "success" => true,
            "message" => "Chatroom created successfully.",  // on success send theses back encode in json
            "recentRoom" => $chatroomName,
            "status" => $status

        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database insert failed: " . $stmt->error   //sends fail message
        ]);
    }

}

$stmt->close();
$mysqli->close();
?>