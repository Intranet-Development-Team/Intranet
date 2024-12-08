<?php
function getAllAssignments(bool $forediting = false): array
{
    if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Assignments/assignments.txt"))
    {
        $assignments = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Assignments/assignments.txt"), true);
    }
    else
    {
        $assignments = [];
    }

    if (!$forediting)
    {
        global $current;
        if (empty($current))
        {
            throw new CurrentSessionInstanceMissingException();
        }

        require_once("IMP.php");
        $IMP = new IMP();

        $subjectsToShow = array_merge(json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/electives.txt"), true), SUBJECTS["cores"], SUBJECTS["others"]);

        foreach ($assignments as $key => &$assignment)
        {
            if (!in_array($assignment["subject"], $subjectsToShow, true))
            {
                unset($assignments[$key]);
            }
            else
            {
                $assignment["content"] = $IMP->line($assignment["content"]);
            }
        }
    }

    return $assignments;
}

function getAssignmentsByRange(int|float $start, int|float $end, bool $forediting = false): array
{
    if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Assignments/assignments.txt"))
    {
        $assignments = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Assignments/assignments.txt"), true);
        $assignments = array_filter($assignments, function ($item) use (&$start, &$end)
        {
            return $item["due"] === "" && $end === INF || $start <= strtotime($item["due"]) && strtotime($item["due"]) < $end;
        });
    }
    else
    {
        $assignments = [];
    }

    if (!$forediting)
    {
        global $current;
        if (empty($current))
        {
            throw new CurrentSessionInstanceMissingException();
        }

        require_once("IMP.php");
        $IMP = new IMP();

        $subjectsToShow = array_merge(json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/electives.txt"), true), SUBJECTS["cores"], SUBJECTS["others"]);

        foreach ($assignments as $key => &$assignment)
        {
            if (!in_array($assignment["subject"], $subjectsToShow, true))
            {
                unset($assignments[$key]);
            }
            else
            {
                $assignment["content"] = $IMP->line($assignment["content"]);
            }
        }
    }
    
    return $assignments;
}

function getAssignmentsHistory(int $version, bool $decodeIML = false): array
{
    $assignments = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/EditAssignments/history.txt"), true)[$version]["content"];

    if ($decodeIML)
    {
        require_once("IMP.php");
        $IMP = new IMP();

        foreach ($assignments as &$assignment)
        {
            $assignment["content"] = $IMP->line($assignment["content"]);
        }
    }

    return $assignments;
}
