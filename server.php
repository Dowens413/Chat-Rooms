<?php
// Configuration
$EOF = 3;
$port = 8080;

// Database Connection
$mysqli = new mysqli("localhost", "436_mysql_user", "123pwd456", "436db");
if ($mysqli->connect_error) {
    die("MySQL connection failed: " . $mysqli->connect_error);
}

// Create server socket
$serverSocket = createServerConnection($port);
socket_listen($serverSocket) or die("Unable to start server, exiting!\n");
echo "Server now running on port $port\n";

$listOfConnectedClients = [];
$clientRooms = [];
$clientUsernames = [];

do {
    $clientsWithData = waitForIncomingMessageFromClients($listOfConnectedClients, $serverSocket);

    if (in_array($serverSocket, $clientsWithData)) {
        $newSocket = socket_accept($serverSocket);
        if (performHandshake($newSocket)) {
            $listOfConnectedClients[] = $newSocket;
            echo "Client connected. Total: " . count($listOfConnectedClients) . "\n";
        } else {
            disconnectClient($newSocket);
        }
    } else {
        foreach ($clientsWithData as $clientSocket) {
            $len = @socket_recv($clientSocket, $buffer, 1024, 0);
            $message = unmask($buffer);

            if ($len === false || $len == 0 || (strlen($message) > 0 && ord($message[0]) == $EOF)) {
                disconnectClient($clientSocket);
            } else if (!empty($message)) {
                $data = json_decode($message, true);
                if (!$data)
                    continue;

                $type = $data["type"] ?? "";
                $cid = spl_object_id($clientSocket);       // stores the client socket 

                if ($type === "join") {              // when joining 
                    $username = $data["username"] ?? "";
                    $chatroom = $data["chatroomName"] ?? "";
                    $key = $data["key"] ?? "";

                    $stmt = $mysqli->prepare("SELECT chatroomKey FROM list_of_chatrooms WHERE chatroomName = ?");   // checks if the the key matches the chatroom
                    $stmt->bind_param("s", $chatroom);
                    $stmt->execute();
                    $stmt->bind_result($dbKey);
                    $stmt->fetch();
                    $stmt->free_result();
                    $stmt->close();

                    if ($dbKey === null || $dbKey === $key) {
                        $clientUsernames[$cid] = $username;
                        $clientRooms[$cid] = $chatroom;

                        // Update chatroom table with screenName instead of username
                        $mysqli->query("DELETE FROM chatroom WHERE screenName = '" . $mysqli->real_escape_string($username) . "'");
                        $mysqli->query("INSERT INTO chatroom (screenName, chatroomName) VALUES ('" . $mysqli->real_escape_string($username) . "', '" . $mysqli->real_escape_string($chatroom) . "')");

                        echo "$username joined $chatroom\n";
                    } else {

                        $out = json_encode(["type" => "error", "message" => "Wrong key for room $chatroom."]);
                        $masked = mask($out);
                        socket_write($clientSocket, $masked, strlen($masked));


                    }

                } elseif ($type === "room") {         //if type is room  message sent form the socket
                    $out = json_encode(["type" => "room", "status" => $data["status"], "room" => $data["recentRoom"]]);
                    foreach ($listOfConnectedClients as $client) {
                        $masked = mask($out);
                        socket_write($client, $masked, strlen($masked));    //send to all clients that are connected
                    }





                } elseif ($type === "message") {       // if the message  sent equal type message
                    $username = $clientUsernames[$cid] ?? "unknown";
                    $chatroom = $clientRooms[$cid] ?? null;
                    if ($chatroom) {
                        $out = json_encode(["type" => "message", "from" => $username, "text" => $data["text"]]);
                        foreach ($listOfConnectedClients as $client) {
                            $otherCid = spl_object_id($client);
                            if ($client != $clientSocket && ($clientRooms[$otherCid] ?? null) === $chatroom) {
                                $masked = mask($out);
                                socket_write($client, $masked, strlen($masked));

                            }
                        }
                        // Send back to sender too
                        $masked = mask($out);
                        socket_write($client, $masked, strlen($masked));

                    }
                }
            }
        }
    }
} while (true);
// fucnction given
function createServerConnection($port, $host = '0.0.0.0')
{
    $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($serverSocket, $host, $port);
    return $serverSocket;
}

function waitForIncomingMessageFromClients($clients, $serverSocket)
{
    $readList = $clients;
    $readList[] = $serverSocket;
    $writeList = $exceptionList = [];
    socket_select($readList, $writeList, $exceptionList, NULL);
    return $readList;
}

function disconnectClient($clientSocket)
{
    global $clientRooms, $clientUsernames, $listOfConnectedClients, $mysqli;
    $cid = spl_object_id($clientSocket);
    $username = $clientUsernames[$cid] ?? null;
    if ($username) {

        $mysqli->query("DELETE FROM chatroom WHERE screenName = '" . $mysqli->real_escape_string($username) . "'");
    }
    unset($clientUsernames[$cid]);
    unset($clientRooms[$cid]);

    $key = array_search($clientSocket, $listOfConnectedClients, true);
    if ($key !== false)
        unset($listOfConnectedClients[$key]);

    socket_close($clientSocket);
    echo "Client disconnected.\n";
}

function performHandshake($clientSocket)
{
    $len = @socket_recv($clientSocket, $headers, 1024, 0);
    if ($len === false || $len == 0)
        return false;
    $headers = explode("\r\n", $headers);
    $headerArray = [];
    foreach ($headers as $header) {
        $parts = explode(": ", $header);
        if (count($parts) === 2)
            $headerArray[$parts[0]] = $parts[1];
    }
    if (!isset($headerArray['Sec-WebSocket-Key']))
        return false;
    $secKey = $headerArray['Sec-WebSocket-Key'];
    $uuid = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
    $secAccept = base64_encode(pack('H*', sha1($secKey . $uuid)));
    $response = "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $secAccept\r\n\r\n";
    socket_write($clientSocket, $response, strlen($response));
    return true;
}

function unmask($payload)
{
    if (strlen($payload) == 0)
        return "";
    $length = ord($payload[1]) & 127;
    if ($length == 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length == 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }
    $unmasked = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $unmasked .= $data[$i] ^ $masks[$i % 4];
    }
    return $unmasked;
}

function mask($message)
{
    $frame = [129];
    $length = strlen($message);
    if ($length <= 125) {
        $frame[] = $length;
    } elseif ($length <= 65535) {
        $frame[] = 126;
        $frame[] = ($length >> 8) & 255;
        $frame[] = $length & 255;
    } else {
        $frame[] = 127;
        for ($i = 7; $i >= 0; --$i)
            $frame[] = ($length >> ($i * 8)) & 255;
    }
    foreach (str_split($message) as $char)
        $frame[] = ord($char);
    return implode(array_map("chr", $frame));
}
?>