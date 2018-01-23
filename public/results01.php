<?php
    require("path.php");
    require($path."includes/functions.php");
    verifySession();
    render("headerStart", ["title" => "Results 01 - Heaps"]);
    render("headerEnd");

    // results is an array where its first elemenet is number of correct responses
    // and its second element is the feedback for the user
    $results = markQuestions($_POST, 1);

    render("feedback", ["correct" => $results[0]]);

    provideFeedback($results[1]);

    render("results", ["thisLevel" => "01", "nextLevel" => "level02", "nextLevelText" => "Go to Level 02!"]);
    render("footer");
?>
