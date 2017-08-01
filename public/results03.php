<?php
    require("path.php");
    require($path."includes/functions.php");
	verifySession();
    render("headerStart", ["title" => "Results 03 - Directed Graphs"]);
    render("headerEnd");

    // results is an array where its first elemenet is number of correct responses
    // and its second element is the feedback for the user
    $results = markQuestions($_POST, 3);

    render("feedback", ["correct" => $results[0]]);

    provideFeedback($results[1]);

    render("results", ["thisLevel" => "03", "nextLevel" => "index", "nextLevelText" => "Go back to Welcome Page!"]);
    render("footer");
?>