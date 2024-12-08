<?php
function validateDatetime(string $date, string $format = 'Y-m-d H:i:s'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function displayDatetime(string|int $datetimeinput): string
{
    if(is_int($datetimeinput))
    {
        $datetimeinput = date("Y-m-d H:i:s", $datetimeinput);
    }

    $datetime = new DateTime($datetimeinput);

    $display = "";

    if ($datetime)
    {
        $timestamp = $datetime->getTimestamp();
        if (strtotime("yesterday") <= $timestamp && $timestamp < strtotime("yesterday +1 days"))
        {
            $display = "yesterday";
        }
        else if (strtotime("today") <= $timestamp && $timestamp < strtotime("today +1 days"))
        {
            $display = "today";
        }
        else if (strtotime("tomorrow") <= $timestamp && $timestamp < strtotime("tomorrow +1 days"))
        {
            $display = "tomorrow";
        }
        else
        {
            $year = $datetime->format("Y");
            $month = $datetime->format("M");
            $day = $datetime->format("j");

            if (strtotime("Next Monday") <= $timestamp && $timestamp < strtotime("Next Monday +1 days") || strtotime("Last Monday") <= $timestamp && $timestamp < strtotime("Last Monday +1 days"))
            {
                $display = "Monday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Tuesday") <= $timestamp && $timestamp < strtotime("Next Tuesday +1 days") || strtotime("Last Tuesday") <= $timestamp && $timestamp < strtotime("Last Tuesday +1 days"))
            {
                $display = "Tuesday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Wednesday") <= $timestamp && $timestamp < strtotime("Next Wednesday +1 days") || strtotime("Last Wednesday") <= $timestamp && $timestamp < strtotime("Last Wednesday +1 days"))
            {
                $display = "Wednesday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Thursday") <= $timestamp && $timestamp < strtotime("Next Thursday +1 days") || strtotime("Last Thursday") <= $timestamp && $timestamp < strtotime("Last Thursday +1 days"))
            {
                $display = "Thursday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Friday") <= $timestamp && $timestamp < strtotime("Next Friday +1 days") || strtotime("Last Friday") <= $timestamp && $timestamp < strtotime("Last Friday +1 days"))
            {
                $display = "Friday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Saturday") <= $timestamp && $timestamp < strtotime("Next Saturday +1 days") || strtotime("Last Saturday") <= $timestamp && $timestamp < strtotime("Last Saturday +1 days"))
            {
                $display = "Saturday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if (strtotime("Next Sunday") <= $timestamp && $timestamp < strtotime("Next Sunday +1 days") || strtotime("Last Sunday") <= $timestamp && $timestamp < strtotime("Last Sunday +1 days"))
            {
                $display = "Sunday, " . $month . " " . $day . (date("Y") !== $year ? ", " . $year : "");
            }
            else if ($timestamp < strtotime("today"))
            {
                $display = $month . " " . $day . (date("Y") !== $year ? ", " . $year : "") . " (" . floor((strtotime("today") - $timestamp) / 86400) . " days ago)";
            }
            else if ($timestamp > strtotime("today"))
            {
                $display = $month . " " . $day . (date("Y") !== $year ? ", " . $year : "") . " (" . floor(($timestamp - strtotime("today")) / 86400) . " days later)";
            }
        }

        $display .= ($datetimeinput !== $datetime->format("Y-m-d") ? " at " . $datetime->format("H:i") : "");
    }
    
    return $display;
}
