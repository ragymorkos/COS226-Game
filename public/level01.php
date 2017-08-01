<?php
	require("path.php");
	require($path."includes/functions.php");
	verifySession();
	render("headerStart", ["title" => "Level 01 - Heaps"]);
?>
		
<script src="./js/script.js"></script>

<?php
	render("headerEnd");
	render("levelHeaderStart", ["file" => "results01.php"]);

	getLevelQuestions(1);
	
	render("levelHeaderEnd");
	render("footer");
?>