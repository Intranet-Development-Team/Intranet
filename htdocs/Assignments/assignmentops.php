<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Assignments", "Assignments");
require("../CoreLibrary/Assignments.php");

if ($current->accessstatus)
{
    if (isset($_POST["data"]))
    {
        $queue = json_decode($_POST["data"], true);
        if (is_array($queue))
        {
            $return = [];
            if (is_file("../Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"))
            {
                $doneAssignments = (array)json_decode(fileread("../Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"), true);
            }
            else
            {
                $doneAssignments = [];
                if (!is_dir("../Login/Accounts/" . $current->username . "/Assignments"))
                {
                    mkdir("../Login/Accounts/" . $current->username . "/Assignments");
                }
            }
            foreach ($queue as $item)
            {
                if ($item["action"] === "done")
                {
                    if (!in_array(["subject" => $item["assignmentSubject"], "content" => $item["assignmentName"], "due" => $item["assignmentDue"]], $doneAssignments))
                    {
                        $doneAssignments[] = ["subject" => $item["assignmentSubject"], "content" => $item["assignmentName"], "due" => $item["assignmentDue"]];
                    }
                    $return[] = ["status" => "done"];
                }
                else if ($item["action"] === "undone")
                {
                    $InDone = array_search(["subject" => $item["assignmentSubject"], "content" => $item["assignmentName"], "due" => $item["assignmentDue"]], $doneAssignments);
                    if ($InDone !== false)
                    {
                        unset($doneAssignments[$InDone]);
                    }
                    $return[] = ["status" => "undone", "missing" => ($item["assignmentDue"] !== "" && strtotime($item["assignmentDue"]) < strtotime("today"))];
                }
            }
            filewrite("../Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt", json_encode($doneAssignments));
            echo json_encode($return);
        }
    }
}
