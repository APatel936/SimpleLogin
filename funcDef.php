<?php
    
    function authenticate ( $ucid, $pass, $db )
    {
        $s =  "select * from users where ucid='$ucid' and pass='$pass'" ;
        
        echo "<br>SQL Credentials select statement is $s<br>";
        
        ($t = mysqli_query ($db, $s)) or die (mysqli_error($db)) ;
        $num = mysqli_num_rows($t) ;
        
        if ($num == 0)
        {
            //not valid
            return false;
        }
        else
        {
            return true;
        }
    }
    
    function display ($ucid, $account, $box, $number, &$results2 ,$db) //displays transactions for ucid and account
    {
        
        $s1 = "select * from users where ucid = '$ucid'";
        $s2 = "select * from accounts where ucid = '$ucid'";
        
        if (! isset ($box)) //box is unchecked
        {
            $s3 = "select * from transactions where ucid='$ucid' and account='$account' ORDER BY transactions.timestamp DESC LIMIT $number";
        }
        
        if (isset ($box)) //box is checked
        {
            $s3 = "select * from transactions where ucid='$ucid' ORDER BY transactions.timestamp DESC LIMIT $number";
        }
        
        ($t1 = mysqli_query ($db, $s1)) or die (mysqli_error($db)) ; //get users
        ($t2 = mysqli_query ($db, $s2)) or die (mysqli_error($db)) ; //get acc
        ($t3 = mysqli_query ($db, $s3)) or die (mysqli_error($db)) ; //get trans
        
        $num2 = mysqli_num_rows($t2) ;
        $results2.="<br>There were $num2 rows retrieved from the accounts table<br>";
        
        $num3 = mysqli_num_rows($t3) ;
        $results2.="<br>There were $num3 rows retrieved from the transactions table<br><br>";
        
        $results2.="<br>--------------------------------------------------------<br>";
        
        $results2.="<br><br><br>";
        $results2.="accounts follow.<br>";
        $results2.="<table border = 2 width = 30%>";
        $results2.="<tr>";
        $results2.="<th>account</th>";
        $results2.="<th>balance</th>";
        $results2.="</tr>";
        while ($row2 = mysqli_fetch_array($t2, MYSQLI_ASSOC))
        {
            $account   =    $row2["account"];
            $balance    =   $row2["balance"] ;
            $results2.="<tr>";
            $results2.="<td>$account</td>";
            $results2.="<td>$$balance</td>";
            $results2.="</tr>";
        };
        $results2.="</table>";
        
        $results2.="<br><br><br>";
        $results2.="transactions follow.<br>";
        $results2.="<table border = 2 width = 30%>";
        $results2.="<tr>";
        $results2.="<th>account</th>";
        $results2.="<th>amount</th>";
        $results2.="<th>timestamp</th>";
        $results2.="</tr>";
        while ( $row3 = mysqli_fetch_array ($t3, MYSQLI_ASSOC))
        {
            $account   =    $row3["account"];
            $amount    =    $row3["amount"] ;
            $timestamp =    $row3["timestamp"] ;
            $results2.="<tr>";
            $results2.="<td>$account</td>";
            $results2.="<td>$amount</td>";
            $results2.="<td>$timestamp</td>";
            $results2.="</tr>";
        };
        $results2.="</table>";
        
    }
    
    
    function get($fieldname, $db)  #better version of _GET function
    {
        if (!isset( $_GET [$fieldname] ) || $_GET [$fieldname] == "")
        {
            if ($fieldname == "num")
            {
                $v = 3;
                echo "$fieldname is: $v<br>";
                return $v;
            }
            if ($fieldname == "amount")
            {
                $v = 0.00;
                echo "$fieldname is: $v<br>";
                return $v;
            }
            
            if ($fieldname == "acc")
            {
                $v = 0;
                echo "$fieldname is: $v<br>";
                return $v;
            }
            
            if ($fieldname == "emCheck")
            {
                $v = "N";
                echo "$fieldname is: $v<br>";
                return $v;
            }
            
            if ($fieldname == "box")
            {
                $v = "N";
                echo "$fieldname is: $v<br>";
                return $v;
            }
            
            echo "<br><br>The value of $fieldname is either NULL or empty.";
            $v = NULL;
            return $v;
        }
        if($fieldname == "emcheck")
        {
            $v = "Y";
            echo "$fieldname is: $v<br>";
            return $v;
        }
        
        if ($fieldname == "box")
        {
            $v = "Y";
            echo "$fieldname is: $v<br>";
            return $v;
        }
        
        $v = $_GET[$fieldname];
        $v = trim ($v);  #removes white spaces
        $v = mysqli_real_escape_string ($db, $v); #cleans data
        
        echo "The $fieldname is: $v<br>";
        return $v;
    }
    
    
    
    function transact ($ucid, $account, $mailbox, $amount, $number, &$results, $db, $box)
    {
        //1. check for overdraft w/ select
        $s1 = "select * from accounts where ucid = '$ucid' and account = '$account' and balance + '$amount' >= 0.00";
        
        $t = mysqli_query ($db, $s1) or die (mysqli_error($db));
        $num = mysqli_num_rows($t);
        
        if ($num == 0)
        {
            $results.= "<br>Overdraft<br>";
            return ;
        }
        $results.= "No Overdraft";
        
        //2. insert new transaction into transaction table
        $s2 = "insert into transactions value ('$ucid', '$account', '$amount', NOW(), '$mailbox')";
        
        $results.= "<br>Insert is: $s2";
        
        $t2 = mysqli_query ($db, $s2) or die (mysqli_error($db));
        
        //3. change balance in account table (for that ucid and that account)
        $s3 = "update accounts Set balance = balance + '$amount' where ucid='$ucid' and account='$account'";  
        
        $results.= "<br>Update is: $s3";
        $t3 = mysqli_query ($db, $s3) or die (mysqli_error($db));
        
        display ($ucid, $account, $box, $number, $results2 ,$db);  
        $results.= $results2;
        
        if(isset ($mailbox))
        {
            mailer("", "Display Transactions" ,$results); //insert email address in ""
        }
        
    }
    
    function mailer($to, $subject, $message)
    {
        $headers = "MIME-Verzion: 1.0" . "\r\n";
        $headers.= "Content-type:text/html;charset=UTF-8";
        $headers.= 'From: <>' . "\r\n"; //insert email address in <>
        mail($to, $subject, $message, $headers);
        
    }

?>
