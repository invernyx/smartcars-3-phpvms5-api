<?php
if(!defined('API'))
    exit;

$database->createTable('smartCARS3Sessions','id int(16) AUTO_INCREMENT, dbID int(16), sessionID varchar(64), timestamp int(16), PRIMARY KEY(id)');
$database->execute('DELETE FROM smartCARS3Sessions WHERE timestamp < ?',array(time() - 2592000));

function attemptLogin($results, $loginData, $passwordRequired = true) {
    global $database;
    $return = array();
    if ($results != array()) {
        if (!fetchRetiredPilots) {
            if ($results['retired'] != '0') {
                $return['result'] = 'inactive';
                return $return;
            }
        }
        if ($results['confirmed'] == '0') {
            $return['result'] = 'unconfirmed';
            return $return;
        }
        if ($passwordRequired == true) {
            $md5Hash = md5($loginData['password'] . $results['salt']);
            if ($md5Hash != $results['password']) {
                $return['result'] = 'incorrectPassword';
                return $return;
            }
            $results['session'] = uniqid('', true);
            $results['session'] .= uniqid('', true);
            $results['session'] .= uniqid('', true);
            $database->execute('INSERT INTO smartCARS3Sessions (dbID, sessionID, timestamp) VALUES (?, ?, ?)',array($results['pilotid'],$results['session'],time()));
        }
        else {
            $query = $database->fetch('SELECT * FROM smartCARS3Sessions WHERE sessionID = ?',array($loginData['session']));
            if ($query == array()) {
                $return['result'] = 'invalid';
                return $return;
            }
        }
        $results['result'] = 'ok';
        return $results;
    } else {
        $return['result'] = 'notFound';
        return $return;
    }
}

function generateJSON($data, $sessionNeeded = false) {
    if ($data['result'] != 'ok') {
        switch($data['result']) {
            case 'unconfirmed':
                errorOut(409,'Pilot not confirmed');
                break;
            case 'notFound':
                errorOut(404,'Pilot not found');
                break;
            case 'incorrectPassword':
                errorOut(401,'Incorrect password given');
                break;
            case 'invalid':
                errorOut(401,'Incorrect session given');
                break;
        }
    } else {
        $pilotID = '';
        for($i = strlen($data['pilotid']); $i < pilotIDLength; $i++)
            $pilotID .= '0';
        $pilotID .= $data['pilotid'];
        $return = array('dbID'=>$data['pilotid'], 'airlineCode'=>$data['code'], 'pilotID'=>$pilotID, 'firstName'=>$data['firstname'], 'lastName'=>$data['lastname'], 'email'=>$data['email'], 'rank'=>$data['rank']);
        if ($sessionNeeded == true)
            $return['session'] = $data['session'];
        echo(json_encode($return));
    }
}

if (isset($_POST['password'])) {
    if (strpos($_GET['id'], '@')) {
        $results = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE email=?',array($_GET['id']));
    } else {
        $results = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid=?',array($_GET['id']));
    }
    $results = $results[0];

    generateJSON(attemptLogin($results, array('password'=>$_POST['password'])), true);
}
else {
    if (strpos($_GET['id'], '@')) {
        $results = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE email=?',array($_GET['id']));
    } else {
        $results = $database->fetch('SELECT * FROM ' . dbPrefix . 'pilots WHERE pilotid=?',array($_GET['id']));
    }
    $results = $results[0];

    if ($results != array())
        generateJSON(attemptLogin($results, array('session'=>$_POST['session']), false));
}
?>