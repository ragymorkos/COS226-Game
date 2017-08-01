<?php

require("path.php");

// authenticate using CAS
function authenticate()
{
    session_start();
    if(empty($_SESSION["user"]))
    {
        require($GLOBALS['path'].'includes/CASClient.php');
        $C = new CASClient();
        $_SESSION["user"] = $C->Authenticate();
    }
    echo("<p style=\"text-align: right;\">Welcome, ".$_SESSION["user"]."!</p>");
}

// render template onto page
function render($template, $data = [])
{
    $pathToTemplate = $GLOBALS['path']."templates/".$template.".php";
    if (file_exists($pathToTemplate))
    {
        extract($data);
        require($pathToTemplate);
    }
}

// if user tries to access level or results page while not in session, redirect them to home page
function verifySession()
{
    session_start();
    if(empty($_SESSION["user"]))
    {
        header("Location: http://cos226game.cs.princeton.edu");
        die();
    }
}

function getLevelQuestions($levelNumber)
{
    $questions = getQuestions($levelNumber);

    $random_numbers = [];
    for($i = 0; $i < 3; $i += 1)
    {
        $question_number = mt_rand(0, sizeof($questions) - 1);
        while(in_array($question_number, $random_numbers))
        {
            $question_number = mt_rand(0, sizeof($questions) - 1);
        }
        $random_numbers[] = $question_number;

        $question_number_string = (string)$question_number;
        echo "<input type=\"hidden\" name=\"q".(string)$i."\" value=\"".$question_number_string."\">";

        $answers = array_slice($questions[$question_number], 1, -1);
        shuffle($answers);
        echo $questions[$question_number][0]."<br><br>";
        for($j = 0; $j < sizeof($answers); $j += 1)
        {
            $s = "<input name=\"".$question_number_string."\" type=\"radio\" id=\"".(string)$i.(string)$j."\" class=\"css-checkbox\" value=\"".$answers[$j]."\"/><label for=\"".(string)$i.(string)$j."\" class=\"css-label radGroup2\">".$answers[$j]."</label><br>";
            if(strlen($answers[$j]) >= 62)
            {
                $s."<br>";
            }
            echo $s;
        }
        echo "<br>";
    }
}

function markQuestions($post, $levelNumber)
{
    $questions = getQuestions($levelNumber);
    $feedback = [];
    $keys = [];
    $correct = 0;
    $time = 0;
    foreach($post as $key => $value)
    {
        if($key === "timeElapsed")
        {
            $time = $value;
        }
        else if ($key[0] !== "q")
        {
            $question = $questions[(int)$key];
            if ($question[sizeof($question) - 1] === $value)
            {
                $correct += 1;
            }
            else
            {
                $keys[] = $key;
                $feedback[] = [$question[0], $value, $question[sizeof($question) - 1]];
            }
        }
    }
    if (sizeof($post) !== 7)
    {
        foreach($post as $key => $value)
        {
            if($key[0] === 'q' && !in_array($value, $keys))
            {
                $question = $questions[(int)$value];
                $feedback[] = [$question[0], "", $question[sizeof($question) - 1]];
            }
        }
    }

    // UPDATE STATS
    $input = fopen($GLOBALS['path'].'database.csv', 'r') or die("can't open file");
    $output = fopen($GLOBALS['path'].'temp.csv', 'w') or die("can't open file"); 

    // calculate score for user
    if($correct === 0)
    {
        $new_score = 0;
    }
    else
    {
        $new_score = (int)((($correct / 4.0) + ($time / 180.0)) * 1000.0);
    }
    
    // see if highest score should change
    $leaderboard = fgetcsv($input);
    if ((int)$leaderboard[2] < $new_score)
    {
        $leaderboard[2] = (string)$new_score;
        $new_total = 0;
        for($i = 2; $i < sizeof($leaderboard); $i += 1)
        {
            $new_total += $leaderboard[$i];
        }
        $leaderboard[1] = (int)((float)$new_total / (sizeof($leaderboard) - 2));
    }
    fputcsv($output, $leaderboard);

    // update user score
    while (($line = fgetcsv($input)) !== false)
    {
        if($line[0] === $_SESSION["user"])
        {
            if ((int)$line[2] < $new_score)
            {
                $line[2] = (string)$new_score;
                $new_total = 0;
                for($i = 2; $i < sizeof($line); $i += 1)
                {
                    $new_total += $line[$i];
                }
                $line[1] = (int)((float)$new_total / (sizeof($line) - 2));
            }
        }
        fputcsv($output, $line);
    }

    //close both files
    fclose($input);
    fclose($output);

    //clean up
    unlink($GLOBALS['path'].'database.csv'); // Delete obsolete database
    rename($GLOBALS['path'].'temp.csv', '/n/fs/rmorkos-226/database.csv'); //Rename temporary to new

    // return the number of questions user got correctly and the feedback for questions they got wrong
    return([$correct, $feedback]);
}

function provideFeedback($feedback)
{
    foreach($feedback as $i)
    {
        echo $i[0]."<br><br>";
        echo "Your answer: ".$i[1]."<br><br>";
        echo "Correct answer: ".$i[2]."<br><br><br>";
    }
}

function getQuestions($levelNumber)
{
    if($levelNumber === 1)
    {
        return  
        [
            ["Consider a priority queue using the unordered array implementation with N items. What is the order of growth of inserting an item into this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(1)"],
            ["Consider a priority queue using the unordered array implementation with N items. What is the order of growth for deleting the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(N)"],
            ["Consider a priority queue using the unordered array implementation with N items. What is the order of growth for finding the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(N)"],
            ["Consider a priority queue using the ordered array implementation with N items. What is the order of growth of inserting an item into this priority queue?", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(N)", "O(N)"],
            ["Consider a priority queue using the ordered array implementation with N items. What is the order of growth for deleting the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(1)"],
            ["Consider a priority queue using the ordered array implementation with N items. What is the order of growth for finding the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(1)"],
            ["What is the order of growth of running time for finding the largest M numbers out of N numbers if we are using a sorted linked list/array implementation? Assume we are yet to sort the list using mergesort.", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(NlogN)"],
            ["What is the order of growth of space usage for finding the largest M numbers out of N numbers if we are using a sorted linked list/array implementation? Assume we are yet to sort the list using mergesort.", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(N)"],
            ["What is the order of growth of running time for finding the largest M numbers out of N numbers if we are using a stack/queue implementation?", "O(MN)", "O(M)", "O(NlogM)", "O(logN)", "O(1)", "O(MN)"],
            ["What is the order of growth of space usage for finding the largest M numbers out of N numbers if we are using a stack/queue implementation?", "O(MN)", "O(M)", "O(NlogM)", "O(logN)", "O(1)", "O(M)"],
            ["What is the order of growth of running time for finding the largest M numbers out of N numbers if we are using a binary heap implementation?", "O(MN)", "O(M)", "O(NlogM)", "O(logN)", "O(1)", "O(NlogM)"],
            ["What is the order of growth of space usage for finding the largest M numbers out of N numbers if we are using a binary heap implementation?", "O(MN)", "O(M)", "O(NlogM)", "O(logN)", "O(1)", "O(M)"],
            ["What is a complete binary tree?", "A binary tree that is perfectly balanced, even for the bottom level", "A binary tree that is balanced for at least logN of its elements", "A binary tree that is balanced for at least half of its elements", "A binary tree that is balanced for at least 9/10 of its elements", "A binary tree that is perfectly balanced, except maybe for bottom level", "A binary tree that is perfectly balanced, except maybe for bottom level"],
            ["What is the height of a complete binary tree with N nodes?", "N", "N^2", "NlgN", "&lfloor;lgN&rfloor;", "lgN", "&lfloor;lgN&rfloor;"],
            ["Where is the largest key located in a max binary heap?", "The root", "The leftmost leaf", "The rightmost leaf", "The rightmost child", "The leftmost child", "The root"],
            ["When using array indices to use a binary heap, what is the index of the parent of a node located at index k?", "sqrt(k)", "lg(k)", "2lg(k)", "k/2", "k/2 + 1", "k/2"],
            ["When using array indices to use a binary heap, what are the indices of the left and right children, respectively, if the index of the parent is k?", "k^2 and k^2 + 1", "4k and 4k + 1", "2k and 2k + 1", "(k/2)^2 and (k/2)^2 + 1", "8k and 8k + 1", "2k and 2k + 1"],
            ["What is the maximum number of comparisons needed for inserting into a binary heap of size N?", "lgN compares", "lgN + 1 compares", "2lgN + 1 compares", "lgN + 2 compares", "2lgN compares", "lgN + 1 compares"],
            ["In a max binary heap, a parent’s key becomes smaller than one or both of its children. What is the update rule to restore correct heap ordering?", "Exchange key in parent with key in smaller child. Repeat until heap order is restored", "Exchange key in parent with key in larger child. Repeat until heap order is restored", "Exchange key in grandparent with key in smaller child. Repeat until heap order is restored.", "Exchange key in grandparent with key in larger child. Repeat until heap order is restored", "Exchange key in parent with key in larger grandchild. Repeat until heap order is restored", "Exchange key in parent with key in larger child. Repeat until heap order is restored"],
            ["In a max binary heap, how do we delete the maximum element?","Exchange root with node at end, then sink it down", "Exchange root with node at end, then swim it up", "Exchange root with larger child, then sink it down", "Exchange root with smaller child, then sink it down", "Exchange root with larger child, then swim it up", "Exchange root with node at end, then sink it down"],
            ["What is the maximum number of comparisons needed to delete maximum item in a max binary heap?", "lgN compares", "lgN + 1 compares", "2lgN + 1 compares", "lgN + 2 compares", "2lgN compares", "2lgN compares"],
            ["Consider a priority queue using the binary heap implementation with N items. What is the order of growth of inserting an item into this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(logN)"],
            ["Consider a priority queue using the binary heap implementation with N items. What is the order of growth for deleting the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(logN)"],
            ["Consider a priority queue using the binary heap implementation with N items. What is the order of growth for finding the max item from this priority queue?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(1)"],
            ["When using heapsort, how many compares and exchanges does the sorting step at most use after putting the input array in heap order?", "&le; lgN compares and exchanges", "&le; NlgN compares and exchanges", "&le; 2NlgN compares and exchanges", "&le; lgN + 1 compares and exchanges", "&le; 2lgN + 2 compares and exchanges", "&le; 2NlgN compares and exchanges"],
            ["When using heapsort, how many compares and exchanges does initial heap construction at most use when taking an input array of arbitrary order?", "&le; N compares and exchanges", "&le; 4N compares and exchanges", "&le; lgN compares and exchanges", "&le; 2N compares and exchanges", "&le; NlgN compares and exchanges", "&le; 2N compares and exchanges"],
            ["Is heapsort an in-place algorithm (i.e. does it need an external array for its processing)?", "Yes", "No", "Depends on the input", "Only if heap elements are strings", "Only if heap elements are integers", "Yes"],
            ["What is the worst case running time of heapsort?", "O(N)", "O(N^2)", "O(NlogN)", "O(logN)", "O(1)", "O(NlogN)"],
            ["Is heapsort a stable sorting algorithm? (i.e. does it completely reshuffle the original input ordering)?", "Yes", "No", "Depends on the input", "Only if heap elements are strings", "Only if heap elements are integers", "No"]
        ];
    }
    else if($levelNumber === 2)
    {
        return  
        [
            ["In the “list of edges” implementation for an undirected graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for an undirected graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(V^2)"],
            ["In the “adjacency lists” implementation for an undirected graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E + V)"],
            ["In the “list of edges” implementation for an undirected graph, what is the order of growth for adding an edge?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “adjacency matrix” implementation for an undirected graph, what is the order of growth for adding an edge? Does it allow parallel edges?", "O(1) and does NOT allow parallel edges", "O(1) and DOES allow parallel edges", "O(E) and does NOT allow parallel edges", "O(E) and DOES allow parallel edges", "O(V) and DOES allow parallel edges", "O(1) and does NOT allow parallel edges"],
            ["In the “adjacency lists” implementation for an undirected graph, what is the order of growth for adding an edge?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “list of edges” implementation for an undirected graph, what is the order of growth for finding if an edge exists between two vertices?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for an undirected graph, what is the order of growth for finding if an edge exists between two vertices?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “adjacency lists” implementation for an undirected graph, what is the order of growth for finding if an edge exists between two vertices?", "O(deg(V))", "O(V)", "O(E)", "O(E^2)", "O(V^2)", "O(deg(V))"],
            ["In the “list of edges” implementation for an undirected graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for an undirected graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(V)"],
            ["In the “adjacency lists” implementation for an undirected graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(deg(V))", "O(V)", "O(E)", "O(E^2)", "O(V^2)", "O(deg(V))"],
            ["Consider depth first search on an undirected graph G to find all the paths from source s to every other vertex. What is the order of growth for this depth first search?", "O(Sum of degrees of all vertices)", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(Sum of degrees of all vertices)"],
            ["After depth first search is done on an undirected graph, what is the order of growth for finding if vertex v is conncted to the source s?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["After depth first search is done on an undirected graph, what is the order of growth for finding the path between vertex v and source s?", "O(length of path)", "O(1)", "O(deg(V))","O(length of path)"],
            ["If we want to find the shortest path in an undirected graph, do we use Depth First Search (DFS) or BFS (BFS)? How much time does the algorithm take to find it?", "BFS in O(E + V)", "BFS in O(V)", "DFS in O(E)", "DFS in O(V)", "BFS in O(E^2)", "BFS in O(E + V)"]
        ];
    }
    else
    {
        return
        [
            ["In the “list of edges” implementation for a directed graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for a directed graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(V^2)"],
            ["In the “adjacency lists” implementation for a directed graph, what is the space cost?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E + V)"],
            ["In the “list of edges” implementation for a directed graph, what is the order of growth for adding an edge?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “adjacency matrix” implementation for a directed graph, what is the order of growth for adding an edge? Does it allow parallel edges?", "O(1) and does NOT allow parallel edges", "O(1) and DOES allow parallel edges", "O(E) and does NOT allow parallel edges", "O(E) and DOES allow parallel edges", "O(V) and DOES allow parallel edges", "O(1) and does NOT allow parallel edges"],
            ["In the “adjacency lists” implementation for a directed graph, what is the order of growth for adding an edge?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “list of edges” implementation for a directed graph, what is the order of growth for finding if an edge exists between two vertices?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for a directed graph, what is the order of growth for finding if an edge exists between two vertices?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(1)", "O(1)"],
            ["In the “adjacency lists” implementation for a directed graph, what is the order of growth for finding if an edge exists between two vertices?", "O(outdeg(V))", "O(V)", "O(E)", "O(E^2)", "O(V^2)", "O(outdeg(V))"],
            ["In the “list of edges” implementation for a directed graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E)"],
            ["In the “adjacency matrix” implementation for a directed graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(V)"],
            ["In the “adjacency lists” implementation for a directed graph, what is the order of growth for iterating over all vertices adjacent to vertex v?", "O(outdeg(V))", "O(V)", "O(E)", "O(E^2)", "O(V^2)", "O(outdeg(V))"],
            ["How does topological sorting work on a DAG?", "Run DFS on a directed graph, then return vertices in reverse postorder", "Run BFS on a directed graph, then return vertices in reverse postorder", "Run DFS on a directed graph, then return vertices in original postorder", "Run BFS on a directed graph, then return vertices in original postorder", "Run DFS on a directed graph, then return vertices in reverse postorder"],
            ["How does Kosaraju-Sharir algorithm for finding connected components within a DAG work?", "Compute topological order then run DFS, considering vertices in reverse topolgical order", "Compute topological order then run BFS, considering vertices in reverse topolgical order", "Compute topological order then run DFS, considering vertices in original topolgical order", "Compute topological order then run BFS, considering vertices in original topolgical order", "Compute topological order then run DFS, considering vertices in reverse topolgical order"],
            ["What is the order of growth for Kosaraju-Sharir algorithm of finding connected components within a DAG?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E + V)"],
            ["What is the order of growth for BFS (breadth first search) for computing the shortest path in a digraph?", "O(E)", "O(V)", "O(E + V)", "O(E^2)", "O(V^2)", "O(E + V)"],
            ["Is finding the multi-source shortest paths problem for a digraph possible? If so, how is it done?", "Yes; use BFS, but initialize by enqueuing all source vertices", "No, it is not possible.", "Yes; use DFS, but initialize by enqueuing all source vertices", "Yes; use BFS, but initialize by appending all source vertices to a stack", "Yes; use DFS, but initialize by appending all source vertices to a stack", "Yes; use BFS, but initialize by enqueuing all source vertices"]
        ];
    }
}

?>
