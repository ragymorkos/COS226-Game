<?php
	require("path.php");
	require($path."includes/functions.php");
	verifySession();
	render("headerStart", ["title" => "Level 03 - Directed Graphs"]);
?>
		
<script src="./js/script.js"></script>

<?php
	render("headerEnd");
	render("levelHeaderStart", ["file" => "results03.php"]);

	getLevelQuestions(3);
	
	render("levelHeaderEnd");
	render("footer");
?>