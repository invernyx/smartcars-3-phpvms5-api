<?php
$database->createTable('smartCARS3_Sessions', 'pilotID int(11) NOT NULL, sessionID varchar(256) NOT NULL, expiry int(11) NOT NULL, PRIMARY KEY(pilotID, sessionID)');
$database->execute('DELETE FROM smartCARS3_Sessions WHERE expiry < ?', array(time()));

if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    error(405, 'POST request method expected, received a ' . $_SERVER['REQUEST_METHOD'] . ' request instead.');
}
if($_POST['session'] === null)
{
    error(400, 'Session is a required field (type `string`)');
}
assertData($_POST, array('session' => 'string'));

$session = explode('.', $_POST['session']);
if(count($session) !== 3)
{
    error(400, 'The session provided was not in valid JWT format');
}
$session[0] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[0])), true);
$session[1] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[1])), true);
if($session[0] === null || $session[1] === null)
{
    error(400, 'The session provided was not in valid JWT format');
}
if($session[0]['alg'] !== 'HS256' || $session[0]['typ'] !== 'JWT')
{
    error(401, 'The session given was not signed by this website');
}
if($session[1]['sub'] === null || $session[1]['exp'] === null)
{
    error(401, 'The session given was not signed by this website');
}
$validSessions = $database->fetch('SELECT sessionID FROM smartCARS3_Sessions WHERE pilotID=? AND expiry=? AND sessionID=?', array($session[1]['sub'], $session[1]['exp'], $_POST['session']));
if(count($validSessions) === 0)
{
    error(401, 'The session given was not valid');
}
$user = $database->fetch('SELECT id, pilot_id as pilotid, name, avatar, email, password FROM ' . dbPrefix . 'users WHERE id=?', array($session[1]['sub']));
if($user === array())
{
    error(500, 'The session was found, but there was no valid pilot. Please report this to the VA');
}
$user = $user[0];
$airline = $database->fetch('SELECT icao FROM ' . dbPrefix . 'airlines WHERE id=(SELECT airline_id FROM ' . dbPrefix . 'users WHERE id = ?)', array($user['id']));
$rank = $database->fetch('SELECT name, image_url as rankImage FROM ' . dbPrefix . 'ranks WHERE id=(SELECT rank_id FROM ' . dbPrefix . 'users WHERE id = ?)', array($user['id']));
if($airline === array() || $rank === array())
{
    error(500, 'The session was found, but there was no valid pilot. Please report this to the VA');
}
$airline = $airline[0];
$rank = $rank[0];

$avatar = null;
if($user['avatar'] !== null) {
    $avatar = sprintf(
        "%s://%s/public/uploads/%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $user['avatar']
    );
}

$rankImage = null;
if(strpos($rank['rankImage'], '/') === 0)
{
    if(file_exists(webRoot . $rank['rankImage']))
    {
        $rankImage = sprintf(
            "%s://%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        ) . $rank['rankImage'];
    }
}
else if ($rank['rankImage'] !== null) {
    $rankImage = $rank['rankImage'];
}

$pilotIDPadding = 4;
$pilotIDSetting = $database->fetch('SELECT value FROM ' . dbPrefix . 'settings WHERE id="pilots_id_length"');
if($pilotIDSetting !== array()) {
    $pilotIDPadding = intval($pilotIDSetting[0]['value']);
}

echo(json_encode(array(
    'dbID' => $user['id'],
    'pilotID' => $airline['icao'] . str_pad($user['pilotid'], $pilotIDPadding, "0", STR_PAD_LEFT),
    'firstName' => explode(' ', $user['name'])[0],
    'lastName' => explode(' ', $user['name'])[1],
    'email' => $user['email'],
    'rank' => $rank['name'],
    'rankImage' => $rankImage,
    'rankLevel' => 0,
    'avatar' => $avatar,
    'session' => $_POST['session']
)));
?>