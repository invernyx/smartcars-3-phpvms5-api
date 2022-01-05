<?php
$database->createTable('smartCARS3_sessions', 'id int(16) AUTO_INCREMENT, dbID int(16), sessionID varchar(256), expiry int(16), PRIMARY KEY(id)');
$database->execute('DELETE FROM smartCARS3_sessions WHERE expiry > ?', array(time()));

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
if($_GET['username'] === null)
{
    error(400, 'Username is a required parameter (type `string`)');
    exit;
}
if($_POST['password'] == null)
{
    error(400, 'Password is a required field (type `string`)');
    exit;
}
assertData($_GET, array('username' => 'string'));
assertData($_POST, array('password' => 'string'));

if(strpos($_GET['username'], '@'))
{
    $result = $database->fetch('SELECT pilotid, firstname, lastname, email, rank, retired, confirmed, password, salt FROM ' . dbPrefix . 'pilots WHERE email=?', array($_GET['username']));
}
else
{
    $result = $database->fetch('SELECT pilotid, firstname, lastname, email, rank, retired, confirmed, password, salt FROM ' . dbPrefix . 'pilots WHERE pilotid=?', array($_GET['username']));
}

if($result === array())
{
    error(404, 'No pilot exists with username ' . $_GET['username']);
    exit;
}
$result = $result[0];

if($result['retired'] !== 0 && !fetchRetiredPilots)
{
    // What do we actually want to do here? It's a valid pilot with no access
}
if($result['confirmed'] === 0)
{
    error(409, 'The pilot has not been confirmed with this airline yet');
    exit;
}
$md5Hash = md5($_POST['password'] . $result['salt']);
if($md5Hash !== $result['password'])
{
    error(401, 'The password was not correct');
    exit;
}
$JWTHeader = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
$JWTPayload = json_encode(array('sub' => $result['pilotid'], 'exp' => time() + 604800));
$JWTHeader = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTHeader));
$JWTPayload = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTPayload));
$JWTSignature = hash_hmac('sha256', $JWTHeader . '.' . $JWTPayload, 'randomKey', true);
$JWTSignature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTSignature));
// Insert JWT to database
// Handle if session for user already exists
echo(json_encode(array(
    'pilotID' => $result['pilotid'],
    'firstName' => $result['firstname'],
    'lastName' => $result['lastname'],
    'email' => $result['email'],
    'rank' => $result['rank'],
    'session' => $JWTHeader . '.' . $JWTPayload . '.' . $JWTSignature
)));
?>