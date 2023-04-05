<?php
echo(json_encode($database->fetch('SELECT subject as title, body, postdate as postedAt, postedby as postedBy FROM ' . dbPrefix . 'news LIMIT 1')));
?>