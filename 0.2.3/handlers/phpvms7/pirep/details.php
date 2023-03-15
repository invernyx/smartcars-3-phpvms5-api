<?php
assertData($_GET, array('id'=>'int'));

$comments = $database->fetch('SELECT comment FROM pirep_comments WHERE pirep_id=? AND user_id=? ORDER BY created_at DESC', array($_GET['id'], $pilotID));
if($pirep === array())
{
    error(404, 'A PIREP with this ID was not found');
    exit;
}
echo(json_encode(array_map(function($comment) {
    return $comment['comment'];
}, $comments)));
?>