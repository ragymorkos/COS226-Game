<form id="questionform" action=<?= htmlspecialchars($file) ?> method="post">
    <input type="hidden" name="timeElapsed" id="timeElapsed">
<div id="Title"><?= htmlspecialchars($title) ?></div><br>
<div id ="status"></div>
<script type="text/javascript">countDown(45,"status");</script>
<br>