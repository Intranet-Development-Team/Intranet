<?php
require_once("CoreFunctions.php");

require_once("DatetimeHandlers.php");

if (empty($current))
{
    throw new CurrentSessionInstanceMissingException();
}

class Mail
{
    public string $subject, $content, $time;
    public User $from;
    public array $to;
    public bool $read, $star;

    public function __construct(string $subject, string $from, array $tos, string $content, string $time, bool $read = false, bool $star = false)
    {
        $this->from = new User($from);
        $tos = array_unique(array_filter($tos));
        $this->to = [];
        foreach ($tos as $to)
        {
            $this->to[] = new User($to);
        }
        $this->subject = $subject;
        $this->content = $content;
        $this->time = $time;
        $this->read = $read;
        $this->star = $star;
    }

    public function getSummaryMailListItem(string $currentfolder, int $id): string
    {
        require_once("/IMP.php");
        $alltousers = [];
        foreach ($this->to as $to)
        {
            $alltousers[] = $to->username;
        }
        $tousers = implode(", ", $alltousers);

        if ($currentfolder != "Sent")
        {
            $notificationsystemoperator = new NotificationSystemOperator();
        }

        return '<a href="?view&folder=' . $currentfolder . '&id=' . $id . '" class="list-group-item list-group-item-action"><div class="w-100 d-flex">' . ($currentfolder != "Sent" && $notificationsystemoperator->getNotificationCountByIDnData("new_mail", $this->from->username . "_" . $this->time . "_" . $this->subject) ? '<span class="badge bg-danger rounded-pill me-2 align-self-center">New</span>' : '') . (!$this->read ? '<i class="bi bi-circle-fill me-2 text-primary align-self-center" style="font-size:.75rem;"></i>' : '') . ($this->star ? '<i class="bi bi-star-fill me-2 align-self-center" style="color:var(--bs-yellow);"></i>' : '') . '<b style="font-weight:500;" class="me-3 text-truncate">' . ($currentfolder == "Sent" ? "To $tousers" : $this->from->usernamefordisplay) . '</b><b class="flex-grow-1 text-truncate">' . (empty($this->subject) ? "No subject" : $this->subject) . '</b><span class="text-secondary flex-shrink-0">' . ucfirst(displayDatetime($this->time)) . '</span></div><i class="me-3 d-block text-truncate">' . substr(strip_tags(str_replace("<br>", " ", $this->content)), 0, 200) . '</i></a>';
    }

    public function getDetailedMailPage(string $currentfolder, int $id): string
    {
        $tousers = "";
        foreach ($this->to as $to)
        {
            $tousers .= '<div class="card d-inline-flex flex-row p-2 me-2"><img src="' . $to->getpfp() . '" class="border border-1 rounded-circle me-2 align-self-center" style="width:2rem;"><span class="align-self-center">' . $to->usernamefordisplay . '</span></div>';
        }
        $tousers = '<div class="d-flex flex-wrap mb-2"><span class="me-2 align-self-center fw-bold">To:</span>'.$tousers.'</div>';

        if ($currentfolder != "Sent")
        {
            $notificationsystemoperator = new NotificationSystemOperator();
            $notificationsystemoperator->removeNotificationByIDnData("new_mail", $this->from->username . "_" . $this->time . "_" . $this->subject);
        }

        return '<h1 class="text-break">' . (empty($this->subject) ? "No subject" : $this->subject) . '</h1><hr><div class="d-flex"><div class="d-flex flex-wrap mb-2 flex-fill"><span class="me-2 align-self-center flex">From:</span><div class="card d-inline-flex flex-row p-2 me-2"><img src="' . $this->from->getpfp() . '" class="border border-1 rounded-circle me-2 align-self-center" style="width:2rem;"><span class="align-self-center">' . $this->from->usernamefordisplay . '</span></div></div><form method="post" class="text-end align-self-center"><div class="btn-group d-inline-flex flex-start"><a class="btn" title="Reply" href="/ReplyMail?folder=' . urlencode($currentfolder) . '&id=' . urlencode($id) . '"><i class="bi bi-reply"></i></a><a class="btn" title="Forward" href="/ForwardMail?folder=' . urlencode($currentfolder) . '&id=' . urlencode($id) . '"><i class="bi bi-forward"></i></a><button class="btn" title="Mark as Unread" name="Unread"><i class="bi bi-app-indicator"></i></button>' . ($this->star ? '<button class="btn" title="Unstar" name="Unstar"><i class="bi bi-star-half"></i></button>' : '<button class="btn" title="Star" name="Star"><i class="bi bi-star-fill"></i></button>') . '<button class="btn" title="Delete" name="Delete"><i class="bi bi-trash text-danger"></i></button></div></form></div>' . $tousers . '<h6 class="text-secondary text-end">' . ucfirst(displayDatetime($this->time)) . '</h6><hr><div class="text-break">' . $this->content . '</div>';
    }

    public function sendMail(bool $savesent = true): void
    {
        global $current;
        foreach ($this->to as $target)
        {
            $tempself = clone $this;
            if (!is_dir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username))
            {
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username);
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username . "/Inbox");
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username . "/Sent");
            }
            filewrite($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username . "/Inbox/" . (count(array_diff(scandir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $target->username . "/Inbox"), array('..', '.'))) + 1) . ".txt", serialize($tempself));
        }

        if ($savesent)
        {
            if (!is_dir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username))
            {
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username);
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username . "/Inbox");
                mkdir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username . "/Sent");
            }

            $readstatus = $this->read;
            $this->read = true;
            filewrite($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username . "/Sent/" . (count(array_diff(scandir($_SERVER["DOCUMENT_ROOT"] . "/Mail/Mails/" . $current->username . "/Sent"), array('..', '.'))) + 1) . ".txt", serialize($this));
            $this->read = $readstatus;
        }

        $newmailnotification = new Notification("New Mail", "You've received a mail from <b>" . $this->from->username . "</b> with " . (empty($this->subject) ? "no subject" : "the subject: <i>" . $this->subject . "</i>") . ".", "Mail", "new_mail", $this->from->username . "_" . $this->time . "_" . $this->subject);
        $stringUsers = [];
        foreach ($this->to as $to)
        {
            $stringUsers[] = $to->username;
        }
        $newmailnotification->pushOnceIfNeeded($stringUsers);

        return;
    }

    public function getQuotedMail(): string
    {
        return '<h6>On ' . date("l, M j, Y", strtotime($this->time)) . ' at ' . date("H:i", strtotime($this->time)) . ', <b>' . $this->from->username . '</b> wrote:</h6><blockquote class="bg-body-tertiary text-break"><h3>' . (empty($this->subject) ? "No subject" : $this->subject) . '</h3>' . $this->content . '</blockquote>';
    }
}
