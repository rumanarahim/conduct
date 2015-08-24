#Conduct Test
##Installation:
- Import the sql script and move the php files into the web folder of any LAMP installation. 
- Change the email addresses in the 'agents' table to your preferred address
- Edit the config.php file to include the connection details of your database and email SMTP configuration. It is currently set to use gmail’s SMTP server.
- The email functionality is from http://phpmailer.worxware.com/ and is in the PHPMailer-master folder
- To access the form, open form.php in a browser

##Changes to Database:
All the tables are in a database called 'conduct'. Three extra fields has been added to the 'agents' table:
- assigned: This field is used to track who to assign the next lead to
- lead_email_sent: This field is set to 1 if the email has been sent to the lead successfully
- agent_email_sent: This field is set to 1 if the email has been sent to the assigned agent successfully

The last two fields are useful for debugging purposes, in case an email has not been sent correctly.

A new table has also been added called 'backlog' which holds the leads that has been submitted when no agents were active. This has to be processed separately via a cron job when agents become available.

##How it Works:
The user first fills up the lead form and hits submit.
The application looks for available active agents. 
- If there are no available agents the lead is inserted into the ‘backlog’ table which will probably use a cron job for assigning the leads to agents when they become available.
- If there is only one agent, all leads will be sent to this particular agent.
- If there is more than one agent, the leads will be sent to the active agents with the least number of assigned leads and an agent that has not been last assigned the lead. This is done to ensure that agents do not receive consecutive emails.

Before the assignment takes place, a normalization process takes place to take into account new or newly activated agents. In the normalization process, the field ‘assigned’ for the new agent will be set to the maximum value of ‘assigned’ minus 1. This ensures that the new agent is the first one to be picked up for the next lead and after being assigned this lead, it will match the rest of the agents and will now form part of the distribution. Without the normalization process, new agents will unfairly receive more leads just so that it appears 'equal' to the other agents. For example, assume all agents have 500 leads assigned each since they started 3 months ago and then a new agent is added. Without the normalization process this new agent will have to process 500 leads to catch up with the rest. Instead the normalization process sets 'assigned' to 499 for the new agent thus ensuring that it gets picked for the next lead. After being assigned the lead, the number will increment to 500 and then it will now be part of the distribution and will receive leads equally in a fair manner.

When agents are deactivated, they are no longer in the list of possible candidates when assigning leads.

##Locking:
In order to prevent multiple submissions taking place simultaneously, file locking is being used to ensure that only one transaction takes place at a time. 

On clicking submit, a file (lock.txt) is updated in the system and set to ‘locked’. After the processing has occurred, the file is set to ‘unlocked’ enabling the next transaction to take place. If on clicking submit, the file is locked, the process will sleep for a random amount of microseconds ranging from 1 to 500000. After that it will check the file again (lock.txt) and this process will take place infinitely until the file has been set to ‘unlocked’. 

##Possible Improvements:
- Object-oriented programming: The entire application could be written using object oriented programming with separate classes for Agent, Lead, Agents, Leads etc. Since the focus of this task was on the distribution of leads, I left it out.
- Cron job: The script to process the backlog needs to be written and could follow the same logic of the main application without the need to normalize since all the leads will be processed in a batch and there is no need to account for newly activated or newly added agents.
- Normalize: There is no need to normalize every time a form is submitted. Rather the normalization should take place every time an agent is re-activated or added. Since normalization was important to ensure fair distribution and the feature to add/enable agents was not part of the scope of this task, this function is being called every time a form is submitted.
- Locking: There are many other ways to do this such as PHP flock() function and MySQL GET_LOCK().



