# EnterpriseDocStorage

   In this project for Enterprise Software Engineering, we were tasked with starting
up our own linux VM, and then given vague directions to use PHP to connect to our
professor's "black-box" server (we were only given the endpoints and had to design 
the software around what they responded with when we attempted to reach them). The 
idea was to connect to his server, recieve a number of files (randomly generated PDFs),
store them in a database, and index them based on however many attributes we decided upon.

   The catch was that your program was to run at least once per day (mine ran once an hour)
on its own. He would enforce this by asking us to submit our crontab and check his API 
connection logs to see if we were connecting on an obvious schedule.

   It was very stressful to trust that the software was robust enough to handle errors on its
own, but I overdid the database reporting enough so that any errors were very easy to trace
back (it was almost always due to the session not being closed before trying to open a new session).
