<!--
Solution to Conduct's Question
Author: Rumana Rahim 
-->
<!DOCTYPE HTML> 
<html>
    <head>
        <style>
            .error {color: #FF0000;}
        </style>
    </head>
    <body> 

<?php

// define variables and set to empty values
$firstnameErr = $lastnameErr = $emailErr = $mobileErr = "";
$firstname = $lastname = $email = $mobile = $message = "";

//Validate input data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   if (empty($_POST["firstname"])) {
     $firstnameErr = "First name is required";
   } else {
     $firstname = test_input($_POST["firstname"]);
     // check if name only contains letters and whitespace
     if (!preg_match("/^[a-zA-Z]*$/",$firstname)) {
       $firstnameErr = "Only letters allowed"; 
     }
   }
   
   if (empty($_POST["lastname"])) {
     $lastnameErr = "Last name is required";
   } else {
     $lastname = test_input($_POST["lastname"]);
     // check if name only contains letters and whitespace
     if (!preg_match("/^[a-zA-Z]*$/",$lastname)) {
       $lastnameErr = "Only letters allowed"; 
     }
   }
   
   if (empty($_POST["email"])) {
     $emailErr = "Email is required";
   } else {
     $email = test_input($_POST["email"]);
     // check if e-mail address is well-formed
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $emailErr = "Invalid email format"; 
     }
   }
     
   if (empty($_POST["mobile"])) {
     $mobileErr = "Mobile number is required";
   } else {
     $mobile = test_input($_POST["mobile"]);
     // check if URL address syntax is valid (this regular expression also allows dashes in the URL)
     if (!preg_match("/^[0-9]*$/",$mobile)) {
       $mobileErr = "Only numbers are allowed"; 
     }
   }

   if (empty($_POST["message"])) {
     $message = "";
   } else {
     $message = test_input($_POST["message"]);
   }
}

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>


        <h2>Contact Form</h2>
            <p><span class="error">* required field.</span></p>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                First name: <input type="text" name="firstname" value="<?php echo $firstname;?>">
                <span class="error">* <?php echo $firstnameErr;?></span>
                <br><br>
                Last name: <input type="text" name="lastname" value="<?php echo $lastname;?>">
                <span class="error">* <?php echo $lastnameErr;?></span>
                <br><br>
                Email: <input type="text" name="email" value="<?php echo $email;?>">
                <span class="error">* <?php echo $emailErr;?></span>
                <br><br>
                Mobile: <input type="text" name="mobile" value="<?php echo $mobile;?>">
                <span class="error">* <?php echo $mobileErr;?></span>
                <br><br>
                Message: <textarea name="message" rows="5" cols="40"><?php echo $message;?></textarea>
                <br><br>
  
                <input type="submit" name="submit" value="Submit"> 
            </form>


<?php
require 'config.php';

//Main function
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($firstnameErr == "" && $lastnameErr =="" && $emailErr == "" && $mobileErr =="") {
        while (1){
            if(file_get_contents("lock.txt") == "unlocked"){
                // no lock present, so place one
                file_put_contents("lock.txt", "locked");
                $agentID = findNextAvailableAgent();
                if ($agentID == 0)
                {
                    //No active agents available
                    saveBacklog ($firstname, $lastname, $email, $mobile, $message);
                    echo "<br><p>Thank you for submitting this form.</p><p>There are no agents available at the moment but we will get in touch with you shortly.</p>";
                }
                else 
                {
                    $leadID = saveFormData($firstname, $lastname, $email, $mobile, $message);
                    assignAgent($leadID, $agentID);
                    echo "<br><p>Thank you for submitting this form.</p><p>An agent will be attending to your request shortly.</p>";
                    sendEmail($leadID, $agentID); 
                } 
                // remove the lock
                file_put_contents("lock.txt", "unlocked", LOCK_EX);
                break;
            }
            else
            {
                //lock present so must wait
                usleep(mt_rand(1, 500000));
            }
        }
    }
}

//Save in backlog because no agents are available
function saveBacklog ($firstname, $lastname, $email, $mobile, $message)
{
        global $conn;
        $sql = "INSERT INTO backlog
                (first_name, last_name, email, mobile, message, created)
                VALUES
                (:firstname, :lastname, :email, :mobile, :message, :created)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('firstname'=>$firstname,
                             'lastname'=>$lastname,
                             'email'=>$email,
                             'mobile'=>$mobile,
                             'message'=>$message,
                             'created'=>date ("Y-m-d H:i:s")
                            ));
}

//Save form in leads table and return new lead id
function saveFormData($firstname, $lastname, $email, $mobile, $message)
{
    global $conn;
    
    //echo "Firstname is: ".$firstname."<br>";
    try {
        
        $sql = "INSERT INTO leads
                (first_name, last_name, email, mobile, message, created)
                VALUES
                (:firstname, :lastname, :email, :mobile, :message, :created)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('firstname'=>$firstname,
                             'lastname'=>$lastname,
                             'email'=>$email,
                             'mobile'=>$mobile,
                             'message'=>$message,
                             'created'=>date ("Y-m-d H:i:s")
                            ));
        $id = $conn->lastInsertId();
        return $id;
    }
    catch (PDOException $e)
    {
        echo "Error: ".$e->getMessage();
    }
}

//Return the id of agent assigned or 0 if no agent is available
function findNextAvailableAgent()
{
    global $conn;
    
    if (numAvailableAgents()>0)
    {
        if (numAvailableAgents() == 1)
        {
            //Only 1 agent is available
            $sql = "SELECT id FROM agents WHERE active=1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);
            return $agent['id'];
        }
        else
        {
            //More than 1 agent is available
            normalizeAgents();
            if (is_null(lastAssignedAgent())) //First lead and so no agent has been assigned last
            {
                $sql0 = "SELECT id FROM agents WHERE active = 1 AND assigned = (SELECT MIN(assigned) from agents WHERE active = 1) LIMIT 1";
                $stmt0 = $conn->prepare($sql0);
                $stmt0->execute();
                $agent0 = $stmt0->fetch(PDO::FETCH_ASSOC);
                return $agent0['id'];
            }
            else
            {
                $sql1 = "SELECT id FROM agents WHERE active = 1 AND id != :lastID AND assigned = (SELECT MIN(assigned) from agents WHERE active = 1 AND id != :lastID) LIMIT 1";
                $stmt1 = $conn->prepare($sql1);
                $stmt1->execute(array('lastID'=>  lastAssignedAgent()));
                $agent1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                return $agent1['id'];
            }
        }
    }
    else
    {
        //No agent is available
        return 0;
    }
}

//Returns the id of the agent that has been assigned last; Used to ensure agents don't get consecutive assignments
function lastAssignedAgent()
{
    global $conn;
    $sql = "SELECT agent_id FROM leads WHERE agent_id is not null ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $leads = $stmt->fetch(PDO::FETCH_ASSOC);
    return $leads['agent_id'];
}

//Returns the number of availble agents that is active
function numAvailableAgents()
{
    global $conn;
    $sql = "SELECT SUM(active) as sum_active from agents WHERE active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $agents = $stmt->fetch(PDO::FETCH_ASSOC);
    return $agents['sum_active'];
}

//Retuerns the maximum value of assigned for active agents only
function maxAssigned()
{
    global $conn;
    $sql = "SELECT MAX(assigned) as max_assigned from agents WHERE active != 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $agents = $stmt->fetch(PDO::FETCH_ASSOC);
    return $agents['max_assigned'];
}

//Ensures new agents are part of the distribution; see readme for details
function normalizeAgents()
{
    global $conn;
    
    $max = maxAssigned();
    if ($max > 1)
    {
        $sql = "SELECT id, assigned from agents WHERE active != 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($agents as $agent)
        {
            if (!($agent['assigned'] == $max || $agent['assigned'] == $max-1))
            {
                $sql1 = "UPDATE agents SET assigned = :value WHERE id = :id";
                $stmt1 = $conn->prepare($sql1);
                $stmt1->execute(array('value'=>$max-1, 'id'=>$agent['id']));
            }
        }
    }
}

//Update records to assign an agent to a lead and to increment the assigned field for an agent
function assignAgent($leadID, $agentID)
{
    global $conn;
    $sql = "UPDATE leads SET agent_id = :agentid, modified = :modified WHERE id = :leadid";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array('agentid'=>$agentID, 'leadid'=>$leadID, 'modified'=>date ("Y-m-d H:i:s")));
    
    $sql1 = "UPDATE agents SET assigned = assigned +1 WHERE id = :agentid";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute(array('agentid'=>$agentID));
    
}

//Sends unstyled email to both lead and agent 
//Updates leads table to indicate if the emails were sent or not
function sendEmail($leadID, $agentID)
{
    global $mail, $conn;
    
    $lead_email_sent = 0;
    $agent_email_sent = 0;
    
    $lead = getLeadDetails($leadID);
    $agent = getAgentDetails($agentID);
    
    
    //Email to Lead
    $mail->addAddress($lead['email']);     // Add a recipient
    $mail->addReplyTo('info@contacthq.com.au', 'Information Desk');
    $mail->Subject = 'Thank you for your interest';
    $mail->Body    = 'Hi '.$lead['first_name'].',<br>'.
                     '<p>Thank you for your interest in Contact HQ.</p>'.
                     '<p>Our agent '.$agent['first_name'].' '.$agent['last_name'].' will be attending to your request and will be in touch with you shortly.</p>'.
                     '<p>If you do not hear back from us within 2 business days or if your request is very urgent, feel free to directly contact '.
                     $agent['first_name'].' at '.$agent['email'].' or by calling '.$agent['mobile'].'.</p>';
    $mail->AltBody = 'Hi '.$lead['first_name'].','.
                     'Thank you for your interest in Contact HQ. '.
                     'Our agent '.$agent['first_name'].' '.$agent['last_name'].' will be attending to your request and will be in touch with you shortly. '.
                     'If you do not hear back from us within 2 business days or if your request is very urgent, feel free to directly contact '.
                     $agent['first_name'].' at '.$agent['email'].' or by calling '.$agent['mobile'].'.';

    if(!$mail->send()) {
        echo '<p>An email message with the details could not be sent to you. Kindly contact ConductHQ for more details.</p>';
        //echo 'Mailer Error: ' . $mail->ErrorInfo.'<br>';
        $lead_email_sent = 0;
    } else {
        echo '<p>An email has been sent to you with the details.</p>';
        $lead_email_sent = 1;
    }

    //Email to Agent
    $mail->addAddress($agent['email']);     // Add a recipient
    $mail->addReplyTo($lead['email'],$lead['first_name'].' '.$lead['last_name'] );
    $mail->Subject = 'A lead has been assigned to you';
    $mail->Body    = 'Hi '.$agent['first_name'].',<br>'.
                     '<p>A lead has been assigned to you. The details are as follows:</p>'.
                     '<ul><li>Name: '.$lead['first_name'].' '.$lead['last_name'].'</li>'.
                     '<li>Email: '.$lead['email'].'</li>'.
                     '<li>Mobile: '.$lead['mobile'].'</li>'.
                     '<li>Message: '.$lead['message'].'</li></ul>';
    $mail->AltBody = 'Hi '.$agent['first_name'].','.
                     'A lead has been assigned to you. The details are as follows: '.
                     'Name: '.$lead['first_name'].' '.$lead['last_name'].' | '.
                     'Email: '.$lead['email'].' | '.
                     'Mobile: '.$lead['mobile'].' | '.
                     'Message: '.$lead['message'];

    if(!$mail->send()) {
        $agent_email_sent = 0;
    } else {
        $agent_email_sent = 1;
    }
    
    $sql = "UPDATE leads SET agent_email_sent = :aes, lead_email_sent = :les WHERE id = :leadID";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array('aes'=>$agent_email_sent, 'les'=>$lead_email_sent, 'leadID'=>$leadID));
}

//Returns an array containing a specific lead's details
function getLeadDetails($leadID)
{
    global $conn;
    $sql = "SELECT * FROM leads WHERE id = :leadID";
    $stmt = $conn->prepare($sql);
    $stmt->execute (array('leadID'=>$leadID));
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    return $lead;
}

//Returns an array containing a specific agent's details
function getAgentDetails ($agentID)
{
    global $conn;
    $sql = "SELECT * FROM agents WHERE id = :agentID";
    $stmt = $conn->prepare($sql);
    $stmt->execute (array('agentID'=>$agentID));
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    return $agent;
}

?>