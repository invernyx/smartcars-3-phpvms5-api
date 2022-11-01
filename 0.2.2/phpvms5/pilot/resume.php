<?php
$database->createTable('smartCARS3_Sessions', 'pilotID int(11) NOT NULL, sessionID varchar(256) NOT NULL, expiry int(11) NOT NULL, PRIMARY KEY(pilotID)');
$database->execute('DELETE FROM smartCARS3_Sessions WHERE expiry < ?', array(time()));

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
    exit;
}
if($_POST['session'] === null)
{
    error(400, 'Session is a required field (type `string`)');
    exit;
}
assertData($_POST, array('session' => 'string'));

$session = explode('.', $_POST['session']);
if(count($session) !== 3)
{
    error(400, 'The session provided was not in valid JWT format');
    exit;
}
$session[0] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[0])), true);
$session[1] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[1])), true);
if($session[0] === null || $session[1] === null)
{
    error(400, 'The session provided was not in valid JWT format');
    exit;
}
if($session[0]['alg'] !== 'HS256' || $session[0]['typ'] !== 'JWT')
{
    error(401, 'The session given was not signed by this website');
    exit;
}
if($session[1]['sub'] === null || $session[1]['exp'] === null)
{
    error(401, 'The session given was not signed by this website');
    exit;
}
$validSessions = $database->fetch('SELECT sessionID FROM smartCARS3_Sessions WHERE pilotID=? AND expiry=? AND sessionID=?', array($session[1]['sub'], $session[1]['exp'], $_POST['session']));
if(count($validSessions) === 0)
{
    error(401, 'The session given was not valid');
    exit;
}
$result = $database->fetch('SELECT code, pilotid, firstname, lastname, email, rank FROM ' . dbPrefix . 'pilots WHERE pilotid=?', array($session[1]['sub']));
if($result === array())
{
    error(500, 'The session was found, but there was no valid pilot. Please report this to the VA');
    exit;
}
$result = $result[0];


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
    'dbID' => $dbid,    
    'pilotID' => $pilotid,
    'firstName' => $result['firstname'],
    'lastName' => $result['lastname'],
    'email' => $result['email'],
    'rank' => $result['rank'],
    'rankLevel' => intval($result['ranklevel']),
    'avatar' => $avatar,
    'session' => $_POST['session']
)));
?>