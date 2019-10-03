<?php
    
    error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    ini_set ('display_errors', 1);
    
    include ("account.php" );
    include ("funcDef.php" );
    $db = mysqli_connect ($hostname, $username, $password, $project);
    if (mysqli_connect_errno ())
    { echo "Failed to connect to MySQL: " . mysqli_connect_error ( );
        exit ();
    }
    print "<br>Successfully connected to MySQL.<br>";
    print "<br>--------------------------------------------------------<br>";
    
    mysqli_select_db ($db, $project);
    
    $ucid    =   get("ucid", $db);
    $pass    =   get("pass",$db);
    $account =   get("acc",$db);
    $amount  =   get("amount",$db);
    $number  =   get("num",$db);
    $mailbox =   get("emCheck", $db);
    $box     =   get("box", $db);
    $choice  =   get("choice",$db);

    print "<br>--------------------------------------------------------<br>";
    
    if ( ! authenticate ($ucid, $pass, $db) )     //auth will return t or f
    {
        exit("Bad Credientials");  //kicks out of program
    }
    
    echo "<br>Authenticated<br>";
    
    if($choice == "d")
    {
        display($ucid, $account, $box, $number, $results2, $db);
        echo $results2;
    }
    else if ($choice == "t")
    {
        transact ($ucid, $account, $mailbox, $amount, $number, $results, $db, $box);
        echo $results;  //would spit out all the echos
    }
    
    
    echo "<br>Bye<br>Interaction completed.<br>";
    
    ?>
