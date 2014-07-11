<?php

$deploy_dir = '/var/www/html/myProject'; // No trailing slash

define('TIME_START', microtime(true));

ini_set("display_errors", true);
error_reporting(E_ALL);

function deploy($command, $stopIfStatusNotZero = true)
{
  $executeNext = true;
  $actions = array();
  foreach ($command as $c)
  {
    $output = array();
    $status = 'not-executed';
    $start = microtime(true);

    if ($executeNext)
    {
      exec($c, $output, $status);

      if ($status != 0)
      {
        $executeNext = !$stopIfStatusNotZero;
      }
    }

    $actions[] = array(
      'command' => $c,
      'output'  => $output,
      'status'  => $status,
      'time'    => microtime(true) - $start
    );

  }
  return $actions;
}

if (isset($_POST['sent'])) {

  $actions = deploy(array(

    //Basic git pull
    "cd $deploy_dir && git pull 2>&1",

    //Only for Drupal (Drush): update DB and clear cache
    "cd $deploy_dir && drush updb && drush cc all"

  ), true);

  $success = true;
  foreach ($actions as $a)
  {
    if ($a['status'] != 0)
    {
      $success = false;
    }
  }
}

// Get the date of the last deployed commit
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
    body { font-family: helvetica, arial, sans-serif; }
    time { background: gray; color: white; border-top-left-radius: .5em; border-top-right-radius: .5em; font-size: .8em; font-weight: bold; padding: .3em .6em; display: inline-block;}
    pre { background: #eee; margin-top: 0; padding: .5em; }
    pre.failed { border: 1px solid red; }
    .error { color: red; }
    h1.success { color: rgb(56, 118, 29); }
  </style>
</head>
<body>

  <?php if (isset($_POST['sent'])) : ?>

    <?php if ($success) : ?>
    <h1 class="success">Deployed in <?php echo round(microtime(true) - TIME_START, 3) ?>s</h1>
    <?php else : ?>
    <h2 class='error'>Deploy failed</h2>
    <?php endif ?>

    <?php foreach ($actions as $action) : ?>
      <time><?php echo round($action['time'], 3) . 's | Status code: ' . $action['status'] ?></time>
      <pre class="<?php if ($action['status'] != 0) echo 'failed' ?>">
<strong>www-data $ <?php echo $action['command'] ?></strong>
<?php echo implode("\n", $action['output']) ?>
      </pre>
    <?php endforeach ?>

  <?php endif ?>

  <h5>Last commit deployed from: <?php echo $date ?></h5>
  <form method="post">
      <input type="submit" name="sent" value="Deploy now" />
      <small>Deployment may take a while...</small>
  </form>
</body>
</html>
