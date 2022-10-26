<?php
$database->createTable('smartCARS3_Sessions', 'pilotID int(11) NOT NULL, sessionID varchar(256) NOT NULL, expiry int(11) NOT NULL, PRIMARY KEY(pilotID)');
$database->execute('DELETE FROM smartCARS3_Sessions WHERE expiry < ?', array(time()));

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
if($_POST['password'] === null)
{
    error(400, 'Password is a required field (type `string`)');
    exit;
}
assertData($_GET, array('username' => 'string'));
assertData($_POST, array('password' => 'string'));

if(strpos($_GET['username'], '@'))
{
    $result = $database->fetch('SELECT code, pilotid, firstname, lastname, email, rank, retired, confirmed, password, salt FROM ' . dbPrefix . 'pilots WHERE email=?', array($_GET['username']));
}
else
{
    $result = $database->fetch('SELECT code, pilotid, firstname, lastname, email, rank, retired, confirmed, password, salt FROM ' . dbPrefix . 'pilots WHERE pilotid=?', array($_GET['username']));
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
$expiry = time() + 604800;
$JWTHeader = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
$JWTPayload = json_encode(array('sub' => $result['pilotid'], 'exp' => $expiry));
$JWTHeader = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTHeader));
$JWTPayload = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTPayload));
$JWTSignature = hash_hmac('sha256', $JWTHeader . '.' . $JWTPayload, uniqid('', true), true);
$JWTSignature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTSignature));
$jwt = $JWTHeader . '.' . $JWTPayload . '.' . $JWTSignature;
$database->execute('DELETE FROM smartCARS3_Sessions WHERE pilotID=?', array($result['pilotid']));
$database->insert('smartCARS3_Sessions', array('pilotID' => $result['pilotid'], 'sessionID' => $jwt, 'expiry' => $expiry));

$dbid = intval($result['pilotid']);
$pilotid = $result['code'];
$pilotnum = (string)($dbid + intval(pilotOffset));
while(strlen($pilotnum) < pilotIDLength)
{
    $pilotnum = '0' . $pilotnum;
}
$pilotid .= $pilotnum;

$avatar = null;
$avatarFile = '/lib/avatars/' . $pilotid . '.png';
if(file_exists(webRoot . $avatarFile))
{
    $url = sprintf(
        "%s://%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME']        
      );
    $avatar = $url . $avatarFile;
}

echo(json_encode(array(
    'dbid' => $dbid,
    'pilotID' => $pilotid,
    'firstName' => $result['firstname'],
    'lastName' => $result['lastname'],
    'email' => $result['email'],
    'rank' => $result['rank'],
    'ranklevel' => intval($result['ranklevel']),
    'avatar' => $avatar,
    'session' => $jwt
)));
?>