<?php 
    require("path.php");
    require($path."includes/functions.php"); 
    render("headerStart", ["title" => "Welcome! - The COS226 Game"]);
    render("headerEnd");
    authenticate();
?>

<div id ="Title">The COS226 Game</div>
<p>Welcome to The COS226 Game! You will play against the clock trying to answer a bunch of basic questions on COS226 topics. Each level you play will present you with 3 randomly selected questions that will test your knowledge on that topic. Your scores are featured at the bottom, along with the current highest scores (which you should try to beat). Now, click Play to start!</p>
<div id="CenterButton">
    <form action="level01.php" method="get">
        <input type="submit" class="Button" value="Play!"/>
    </form>
</div><br>

<?php
    // if this is user's first visit, put them in database
    $line = [];
    $file = fopen($path.'database.csv', 'r') or die("can't open file");
    $found_user = false;
    $topics = ["test", "Total Score: ", "Level 01 - Heaps: ", "Level 02 - Undirected Graphs: ", "Level 03 - Directed Graphs: "];
    $leaderboard = fgetcsv($file);
    while (($line = fgetcsv($file)) !== FALSE) 
    {
        if($line[0] === $_SESSION["user"])
        {
            $found_user = true;
            break;
        }
    }
    if(!$found_user)
    {
        fclose($file);
        $file = fopen($path.'database.csv', 'a') or die("can't open file");
        $line = [$_SESSION["user"]];
        for($i = 1; $i < sizeof($topics); $i += 1)
        {
            $line[] = "0";
        }
        fputcsv($file, $line);
    }
    fclose($file);
?>

<div id="Total">
<div class='left'>Leaderboard (Current High Scores)</div><div class='right'>Your Scores</div>
</div><br><br> 

<?php 
    for($i = 1; $i < sizeof($line); $i += 1)
    {
        echo "<div class='left'>".$topics[$i].$leaderboard[$i]."</div>
                <div class='right'>".$topics[$i].$line[$i]."</div><br>";
    } 

    render("footer");
?>