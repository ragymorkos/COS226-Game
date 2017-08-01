</div>
<div id="Results">
    <form id="questionform" action=<?= htmlspecialchars("level".$thisLevel.".php") ?> method="post">
        <input type="submit" class="Button" value="Play Again?" />
    </form><br>
    <form id="questionform" action=<?= htmlspecialchars($nextLevel.".php") ?> method="post">
        <input type="submit" class="Button" value="<?= htmlspecialchars($nextLevelText) ?>" />
    </form>
</div>