<?php

// Change this to your deployment target 
$deploy_dir = '.';

if (isset($_POST['sent'])) {
    $output = array();
    $status = 0;
    $command = "cd $deploy_dir && git pull 2>&1";
    exec($command, $output, $status);

    if ($status == 0) {
      echo "<h1>Deployed!</h1>";
    } else {
      echo "<h2 class='error'>Deploy failed</h2>";
    }

    echo "<pre><strong>www-data \$ $command</strong>\n\n" . implode("\n", $output) . "</pre>";
}

$date = exec("cd $deploy_dir && git log -1 --format=\"%cd\"");
$date = date('Y-m-d H:i:s', strtotime($date));

if (date('Y-m-d H:i:s') == $date) {
  $date = "just now";
}

$date = str_replace(date('Y-m-d'), 'today', $date);

?>
<html>
<head>
  <title>Deploy from git</title>
  <style>
    pre { background: #eee; padding: 5px; }
    .error { color: red; }
  </style>
</head>
<body style="font-family: arial">
    <h5>Last deployed: <?php echo $date ?></h5>
    <form method="post">
        <input type="submit" name="sent" value="Deploy now" />
        <small>Deployment may take a while...</small>
    </form>
</body>
</html>
