<?php
	require("path.php");
	require($path."includes/functions.php");
	verifySession();
	render("headerStart", ["title" => "Level 02 - Undirected Graphs"]);
?>
		
<script src="./js/script.js"></script>

<?php
	render("headerEnd");
	render("levelHeaderStart", ["file" => "results02.php"]);

	getLevelQuestions(2);
	
	render("levelHeaderEnd");
	render("footer");
?>