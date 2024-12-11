<?php
require_once("CoreFunctions.php");

require_once("DatetimeHandlers.php");

function getAllEvents(bool $includebdays = true, bool $forediting = false): array
{
    $events = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Calendar/events.txt"), true);

    if ($includebdays)
    {
        $allbdays = [];
        $allusers = array_diff(scandir($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts"), array('..', '.'));
        foreach ($allusers as $user)
        {
            if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/$user/birthday.txt"))
            {
                $monthdate = date("m-d", fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/$user/birthday.txt"));
                $lastyr = (date("Y") - 1) . "-" . $monthdate;
                if (validateDatetime($lastyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $lastyr, "start" => "00:00", "end" => "23:59"];
                }

                $thisyr = date("Y") . "-" . $monthdate;
                if (validateDatetime($thisyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $thisyr, "start" => "00:00", "end" => "23:59"];
                }

                $nextyr = (date("Y") + 1) . "-" . $monthdate;
                if (validateDatetime($nextyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $nextyr, "start" => "00:00", "end" => "23:59"];
                }
            }
        }
        $events = array_merge($allbdays, $events);
        usort($events, function ($a, $b)
        {
            return strtotime($a["date"] . "T" . $a["start"]) <=> strtotime($b["date"] . "T" . $b["end"]);
        });
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

        foreach ($events as $key => &$event)
        {
            if (!in_array($event["subject"], $subjectsToShow, true))
            {
                unset($events[$key]);
            }
            else
            {
                $event["name"] = $IMP->line($event["name"]);
            }
        }
    }
    return $events;
}

function getEventsByRange(int|float $start, int|float $end, bool $includebdays = true, bool $forediting = false): array
{
    $events = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Calendar/events.txt"), true);

    if ($includebdays)
    {
        $allbdays = [];
        $allusers = array_diff(scandir($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts"), array('..', '.'));
        foreach ($allusers as $user)
        {
            if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/$user/birthday.txt"))
            {
                $monthdate = date("m-d", fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/$user/birthday.txt"));
                $lastyr = (date("Y") - 1) . "-" . $monthdate;
                if (validateDatetime($lastyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $lastyr, "start" => "00:00", "end" => "23:59"];
                }

                $thisyr = date("Y") . "-" . $monthdate;
                if (validateDatetime($thisyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $thisyr, "start" => "00:00", "end" => "23:59"];
                }

                $nextyr = (date("Y") + 1) . "-" . $monthdate;
                if (validateDatetime($nextyr, "Y-m-d"))
                {
                    $allbdays[] = ["subject" => "Other", "name" => "$user's birthday", "date" => $nextyr, "start" => "00:00", "end" => "23:59"];
                }
            }
        }
        $events = array_merge($allbdays, $events);
        usort($events, function ($a, $b)
        {
            return strtotime($a["date"] . "T" . $a["start"]) <=> strtotime($b["date"] . "T" . $b["end"]);
        });
    }
    $events = array_filter($events, function ($item) use (&$start, &$end)
    {
        return $start <= strtotime($item["date"] . "T" . $item["start"]) && strtotime($item["date"] . "T" . $item["start"]) < $end;
    });
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

        foreach ($events as $key => &$event)
        {
            if (!in_array($event["subject"], $subjectsToShow, true))
            {
                unset($events[$key]);
            }
            else
            {
                $event["name"] = $IMP->line($event["name"]);
            }
        }
    }
    return $events;
}

function getCalendarEventsHistory(int $version, bool $decodeIML = false): array
{
    $events = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/EditCalendarEvents/history.txt"), true)[$version]["content"];

    if ($decodeIML)
    {
        require_once("IMP.php");
        $IMP = new IMP();

        foreach ($events as $key => &$event)
        {
            $event["name"] = $IMP->line($event["name"]);
        }
    }

    return $events;
}
