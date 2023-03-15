<?php
$database->createTable('smartCARS3_Sessions', 'pilotID int(11) NOT NULL, sessionID varchar(256) NOT NULL, expiry int(11) NOT NULL, PRIMARY KEY(pilotID)');
$database->execute('DELETE FROM smartCARS3_Sessions WHERE expiry < ?', array(time()));

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
}
if($_GET['username'] === null)
{
    error(400, 'Username is a required parameter (type `string`)');
}
if($_POST['password'] === null)
{
    error(400, 'Password is a required field (type `string`)');
}
assertData($_GET, array('username' => 'string'));
assertData($_POST, array('password' => 'string'));

if(strpos($_GET['username'], '@'))
{
    $user = $database->fetch('SELECT id, pilot_id as pilotid, name, avatar, email, password FROM ' . dbPrefix . 'users WHERE email=?', array($_GET['username']));
}
else
{
    $user = $database->fetch('SELECT id, pilot_id as pilotid, name, avatar, email, password FROM ' . dbPrefix . 'users WHERE pilot_id=?', array($_GET['username']));
}

if($user === array())
{
    error(404, 'No pilot exists with username ' . $_GET['username']);
}
$user = $user[0];

$airline = $database->fetch('SELECT icao FROM ' . dbPrefix . 'airlines WHERE id=(SELECT airline_id FROM ' . dbPrefix . 'users WHERE id = ?)', array($user['id']));
$rank = $database->fetch('SELECT name FROM ' . dbPrefix . 'ranks WHERE id=(SELECT rank_id FROM ' . dbPrefix . 'users WHERE id = ?)', array($user['id']));

$airline = $airline[0];
$rank = $rank[0];

if(!password_verify($_POST['password'], $user['password'])) {
    error(401, 'The password was not correct');
}
$expiry = time() + 604800;
$JWTHeader = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
$JWTPayload = json_encode(array('sub' => $user['pilotid'], 'exp' => $expiry));
$JWTHeader = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTHeader));
$JWTPayload = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTPayload));
$JWTSignature = hash_hmac('sha256', $JWTHeader . '.' . $JWTPayload, uniqid('', true), true);
$JWTSignature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTSignature));
$jwt = $JWTHeader . '.' . $JWTPayload . '.' . $JWTSignature;
$database->execute('DELETE FROM smartCARS3_Sessions WHERE pilotID=?', array($user['pilotid']));
$database->insert('smartCARS3_Sessions', array('pilotID' => $user['pilotid'], 'sessionID' => $jwt, 'expiry' => $expiry));

$avatar = null;
if($user['avatar'] !== null) {
    $avatar = sprintf(
        "%s://%s/public/uploads/%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $user['avatar']
    );
}

echo(json_encode(array(
    'dbID' => $user['id'],
    'pilotID' => $airline['icao'] . str_pad($user['pilotid'], 4, "0", STR_PAD_LEFT),
    'firstName' => explode(' ', $user['name'])[0],
    'lastName' => explode(' ', $user['name'])[1],
    'email' => $user['email'],
    'rank' => $rank['name'],
    'rankLevel' => 0,
    'avatar' => $avatar,
    'session' => $jwt
)));
?>