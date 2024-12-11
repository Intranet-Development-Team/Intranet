<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Calendar", "Calendar");
require_once("../CoreLibrary/CalendarEvents.php");
?>
<!DOCTYPE html>
<html>
<style>
    #calendar {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    #calendar tr,
    #calendar tbody {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        width: 100%;
    }

    caption {
        text-align: center;
        grid-column: 1 / -1;
        font-size: 130%;
        font-weight: bold;
        padding: 0.625em 0;
        text-transform: uppercase;
        color: var(--bs-body-color) !important;
    }

    #calendar td,
    #calendar th {
        padding: 0.3em;
        box-sizing: border-box;
        border: 0.063em solid var(--bs-border-color);
    }

    #calendar .weekdays {
        background: var(--bs-gray-dark);
    }


    #calendar .weekdays th {
        text-align: center;
        line-height: 1.5em;
        border: none;
        padding: 0.625em 0.375em;
        color: #fff;
        font-size: 0.9em;
    }

    #calendar td {
        min-height: 7em;
        display: flex;
        flex-direction: column;
    }

    #calendar .date {
        text-align: center;
        margin-bottom: 0.5em;
        padding: 0.5em;
        width: 1em;
        flex: 0 0 auto;
    }

    #calendar .event {
        font-size: 0.75em;
        padding: 0.3em;
        margin-bottom: 0.4em;
        line-height: 1.2em;
        background-color: var(--bs-secondary-bg);
        color: var(--bs-secondary-color);
        word-break: break-word;
    }

    #calendar .day.active {
        background-color: var(--bs-tertiary-color);
    }

    #calendar .event-desc {
        color: var(--bs-body-color);
    }

    #calendar .other-month {
        background-color: var(--bs-tertiary-bg);
        color: #666;
    }
</style>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
    <header><?= $current->getNavBar() ?></header>
    <?php
    if ($current->accessstatus)
    {
        echo '<h1 style="display:inline-block;">Calendar</h1><a href="/EditCalendarEvents" class="btn" style="float:right;"><i class="bi bi-pencil"></i></a><hr style="margin-top:0.5em;">';
        echo '<div style="overflow: auto;"><table id="calendar" style="min-width:75em;width:100%;"></table></div>';
    ?>
        <?= $current->getFooter() ?>
        <script>
            function daysInMonth(month, year) {
                return new Date(year, month + 1, 0).getDate();
            }
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            var d = new Date();

            var allevents = JSON.parse('<?= addslashes(json_encode(getAllEvents())) ?>');

            function initializeMonthCalendar() {
                document.getElementById("calendar").innerHTML = '<caption style="display:flex;text-transform:none;"><button class="btn" onclick="previous()"><i class="bi bi-caret-left-fill"></i></button><h4 style="display:inline-block;margin:auto;" id="monthyear"></h4><button class="btn" onclick="next()"><i class="bi bi-caret-right-fill"></i></button></caption><tr class="weekdays"><th scope="col">Sunday</th><th scope="col">Monday</th><th scope="col">Tuesday</th><th scope="col">Wednesday</th><th scope="col">Thursday</th><th scope="col">Friday</th><th scope="col">Saturday</th></tr><tr id="week1"></tr><tr id="week2"></tr><tr id="week3"></tr><tr id="week4"></tr><tr id="week5"></tr><tr id="week6"></tr>';
                document.getElementById("monthyear").innerHTML = monthNames[d.getMonth()] + " " + d.getFullYear();
                var monthThis = d.getMonth();
                var nowDayOfWeek = d.getDay();
                var nowDayOfMonth = d.getDate();
                var firstDay = 0;
                for (var i = nowDayOfMonth; i > 0; i--) {
                    newd = new Date(d.getFullYear(), d.getMonth(), i);
                    firstDay = newd.getDay();
                }
                var thisMonthStartDayOfWeek = firstDay;
                var thisMonthDays = daysInMonth(d.getMonth(), d.getFullYear());
                var lastMonthDays = daysInMonth((d.getMonth() == 0 ? 11 : d.getMonth() - 1), (d.getMonth() == 0 ? d.getFullYear() - 1 : d.getFullYear()));
                var nextMonthDays = daysInMonth((d.getMonth() == 11 ? 0 : d.getMonth() + 1), (d.getMonth() == 11 ? d.getFullYear() + 1 : d.getFullYear()));
                var finalDisplayLastMonth = new Array();
                for (var i = 0; i < thisMonthStartDayOfWeek; i++) {
                    finalDisplayLastMonth.unshift(lastMonthDays);
                    lastMonthDays--;
                }
                var finalDisplayThisMonth = new Array();
                for (var i = 1; i <= thisMonthDays; i++) {
                    finalDisplayThisMonth.push(i);
                }

                var finalDisplayNextMonth = new Array();

                while ((finalDisplayLastMonth.length + finalDisplayThisMonth.length + finalDisplayNextMonth.length) % 7 !== 0) {
                    finalDisplayNextMonth.push(i);
                }
                var Index = 0;
                var week = 1;
                for (;;) {
                    Index++;
                    if (Index <= finalDisplayLastMonth.length) {
                        var child = document.createElement("td");
                        var divC = document.createElement("div");
                        child.classList = "day other-month";
                        divC.classList = "date";
                        divC.innerHTML = lastMonthDays + Index;
                        child.appendChild(divC);
                        for (const eve in allevents) {
                            if (((allevents[eve].date.split("-")[0] == d.getFullYear() && allevents[eve].date.split("-")[1] == d.getMonth()) || (allevents[eve].date.split("-")[0] == d.getFullYear() - 1 && allevents[eve].date.split("-")[1] == 12 && d.getMonth() === 0)) && allevents[eve].date.split("-")[2] == lastMonthDays + Index) {
                                var eventSubject = allevents[eve].subject;
                                var eventName = allevents[eve].name;
                                var eventTime = allevents[eve].start + " - " + allevents[eve].end;
                                var EVENT = document.createElement("div");
                                EVENT.classList = "event";
                                var EVENTDESC = document.createElement("div");
                                EVENTDESC.classList = "event-desc";
                                EVENTDESC.innerHTML = (eventSubject != "Other" ? "<b>" + eventSubject + "</b>&nbsp;&nbsp;" : "") + eventName;
                                var EVENTTIME = document.createElement("div");
                                EVENTTIME.classList = "event-time";
                                EVENTTIME.innerHTML = eventTime;
                                EVENT.appendChild(EVENTDESC);
                                EVENT.appendChild(EVENTTIME);
                                child.appendChild(EVENT);
                            } else {
                                continue;
                            }
                        }
                        document.getElementById("week1").appendChild(child);
                    } else {
                        break;
                    }
                }
                for (;;) {
                    if ((Index - 1) % 7 == 0) {
                        week++;
                    }
                    if (Index <= finalDisplayThisMonth.length + finalDisplayLastMonth.length) {
                        var child = document.createElement("td");
                        var divC = document.createElement("div");
                        var nowDate = new Date();
                        if (d.getDate() == Index - finalDisplayLastMonth.length && d.getFullYear() + "-" + d.getMonth() == nowDate.getFullYear() + "-" + nowDate.getMonth()) {
                            child.classList = "day active";
                        } else {
                            child.classList = "day";
                        }
                        divC.classList = "date";
                        divC.innerHTML = Index - finalDisplayLastMonth.length;
                        child.appendChild(divC);
                        document.getElementById("week" + week).appendChild(child);
                        for (const eve in allevents) {
                            if (allevents[eve].date.split("-")[0] == d.getFullYear() && allevents[eve].date.split("-")[1] == d.getMonth() + 1 && allevents[eve].date.split("-")[2] == Index - finalDisplayLastMonth.length) {
                                var eventSubject = allevents[eve].subject;
                                var eventName = (eventSubject != "Other" ? "<b>" + eventSubject + "</b>&nbsp;&nbsp;" : "") + allevents[eve].name;
                                var eventTime = allevents[eve].start + " - " + allevents[eve].end;
                                var EVENT = document.createElement("div");
                                EVENT.classList = "event";
                                var EVENTDESC = document.createElement("div");
                                EVENTDESC.classList = "event-desc";
                                EVENTDESC.innerHTML = eventName;
                                var EVENTTIME = document.createElement("div");
                                EVENTTIME.classList = "event-time";
                                EVENTTIME.innerHTML = eventTime;
                                EVENT.appendChild(EVENTDESC);
                                EVENT.appendChild(EVENTTIME);
                                child.appendChild(EVENT);
                            } else {
                                continue;
                            }
                        }
                    } else {
                        break;
                    }
                    Index++;
                }

                for (;;) {
                    if ((Index - 1) % 7 === 0) {
                        week++;
                    }
                    if (Index <= finalDisplayLastMonth.length + finalDisplayThisMonth.length + finalDisplayNextMonth.length) {
                        var child = document.createElement("td");
                        var divC = document.createElement("div");
                        child.classList = "day other-month";
                        divC.classList = "date";
                        divC.innerHTML = Index - finalDisplayLastMonth.length - finalDisplayThisMonth.length;
                        child.appendChild(divC);

                        for (const eve in allevents) {
                            if (((allevents[eve].date.split("-")[0] == d.getFullYear() && allevents[eve].date.split("-")[1] == d.getMonth() + 2) || (allevents[eve].date.split("-")[0] == d.getFullYear() + 1 && allevents[eve].date.split("-")[1] == 1 && d.getMonth() === 11)) && allevents[eve].date.split("-")[2] == Index - finalDisplayLastMonth.length - finalDisplayThisMonth.length) {
                                var eventSubject = allevents[eve].subject;
                                var eventName = (eventSubject != "Other" ? "<b>" + eventSubject + "</b>&nbsp;&nbsp;" : "") + allevents[eve].name;
                                var eventTime = allevents[eve].start + " - " + allevents[eve].end;
                                var EVENT = document.createElement("div");
                                EVENT.classList = "event";
                                var EVENTDESC = document.createElement("div");
                                EVENTDESC.classList = "event-desc";
                                EVENTDESC.innerHTML = eventName;
                                var EVENTTIME = document.createElement("div");
                                EVENTTIME.classList = "event-time";
                                EVENTTIME.innerHTML = eventTime;
                                EVENT.appendChild(EVENTDESC);
                                EVENT.appendChild(EVENTTIME);
                                child.appendChild(EVENT);
                            } else {
                                continue;
                            }
                        }
                        document.getElementById("week" + week).appendChild(child);
                    } else {
                        break;
                    }
                    Index++;
                }
            }

            function previous() {
                if (d.getMonth() > 0) {
                    d.setMonth(d.getMonth() - 1);
                } else {
                    d.setFullYear(d.getFullYear() - 1);
                    d.setMonth(11);
                }
                initializeMonthCalendar();
            }

            function next() {
                if (d.getMonth() < 11) {
                    d.setMonth(d.getMonth() + 1);
                } else {
                    d.setFullYear(d.getFullYear() + 1);
                    d.setMonth(0);
                }
                initializeMonthCalendar();
            }

            initializeMonthCalendar();
        </script>
    <?php
    }
    else
    {
        echo $current->accessstatusmsg;
    }
    ?>
</body>

</html>