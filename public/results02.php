<?php
    require("path.php");
    require($path."includes/functions.php");
    verifySession();
    render("headerStart", ["title" => "Results 01 - Undirected Graphs"]);
    render("headerEnd");

    // results is an array where its first elemenet is number of correct responses
    // and its second element is the feedback for the user
    $results = markQuestions($_POST, 2);

    render("feedback", ["correct" => $results[0]]);

    provideFeedback($results[1]);

    render("results", ["thisLevel" => "02", "nextLevel" => "level03", "nextLevelText" => "Go to Level 03!"]);
    render("footer");
?>
