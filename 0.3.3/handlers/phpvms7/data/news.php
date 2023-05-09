<?php
$news = $database->fetch('SELECT subject as title, body, created_at as postedAt, user_id FROM ' . dbPrefix . 'news ORDER BY created_at DESC LIMIT 1');
if($news !== array()) {
    $news = $news[0];
    $user = $database->fetch('SELECT name FROM ' . dbPrefix . 'users WHERE id = ?', array($news['user_id']));
    if($user !== array())
        $news['postedBy'] = $user[0]['name'];
    else
        $news['postedBy'] = 'Unknown';
    unset($news['user_id']);
}
echo(json_encode($news));
?>