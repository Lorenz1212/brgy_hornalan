<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Process Diagram</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowchart/1.14.1/flowchart.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        #diagram {
            width: 90%;
            margin: auto;
            text-align: left;
            border: 1px solid #ddd;
            padding: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h2>Process Diagram: Admin Workflow</h2>
    <div id="diagram"></div>
    <script>
        var diagramCode = `st=>start: Admin Logs In
verify=>operation: System Checks Login Details
cond1=>condition: Login Successful?
dashboard=>operation: Redirect to Dashboard
error=>operation: Show Error Message
menu=>operation: Display Sidebar & Navbar
view=>operation: Admin Sees Requests, Officials, Residents, Walk-ins, Graphs, Notifications
select=>operation: Admin Selects a Request
cond2=>condition: Approve or Reject?
approve=>operation: Send Email to Resident & Move to Clearance/Certificate
reject=>operation: Send Email to Resident & Move to Recycle Bin
print=>operation: Print Document & Notify Resident
printed=>operation: Move to Printed Documents
markdone=>operation: Mark as Done & Notify Resident
claiming=>operation: Move to Claiming Section
claimed=>operation: Resident Claims & Final Notification
history=>operation: Move to History
recycle=>operation: Recycle Bin Handling (Restore/Delete)
logout=>operation: Admin Logs Out
end=>end: Session Destroyed & Redirect to Login

st->verify->cond1
cond1(yes)->dashboard->menu->view->select->cond2
cond1(no)->error->st
cond2(yes)->approve->print->printed->markdone->claiming->claimed->history->end
cond2(no)->reject->recycle->end
logout->end`;
        
        flowchart.parse(diagramCode).drawSVG("diagram");
    </script>
</body>
</html>
