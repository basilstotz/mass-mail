<?php
include('lib/stats_queue.php');
?>

<html>
<header>
<meta http-equiv="refresh" content="30; URL=stats.php">
</header>
<body>
<h1>Mail-Queue Statisitics</h1>

<p>Last 24 hours:</p>

<?php stats_queue(); ?>

</body>
</html>