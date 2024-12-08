<?php
enum NotificationType: string
{
    case info = "info";
    case success = "success";
    case warning = "warning";
    case danger = "danger";
}

class Notification
{
    public string $title;
    public string $content;
    public string $page;
    public string $id;
    public string $data;
    public NotificationType $type;
    public bool $banner;

    public function __construct(string $title, string $content, string $page, string $id = "", string $data = "", NotificationType $type = NotificationType::info, bool $banner = true)
    {
        $this->title = $title;
        $this->content = $content;
        $this->page = $page;
        $this->id = $id;
        $this->data = $data;
        $this->type = $type;
        $this->banner = $banner;
    }

    public function ifShow(): bool
    {
        return true;
    }

    public function getPushNotificationDisplay(): string|bool
    {
        if ($this->banner)
        {
            return '<div class="alert alert-' . $this->type->value . ' fade show shadow" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" style="float:right;"></button><h5 class="alert-heading">' . $this->title . '</h5><p style="word-break: break-word;">' . $this->content . '</p></div>';
        }
        else
        {
            return false;
        }
    }

    public function push(array $targetUsers): void
    {
        foreach ($targetUsers as $targetUser)
        {
            $allNotifications = [];
            if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt"))
            {
                $allNotifications = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt"), true);
            }

            array_unshift($allNotifications, serialize($this));
            filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt", json_encode($allNotifications));
        }
    }

    public function pushOnceIfNeeded(array $targetUsers): void
    {
        foreach ($targetUsers as $targetUser)
        {
            $allNotifications = [];
            if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt"))
            {
                $allNotifications = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt"), true);
            }

            $isNotified = false;
            foreach ($allNotifications as $notification)
            {
                $notification = unserialize($notification);
                if ($notification->page === $this->page && $notification->id === $this->id && $notification->data === $this->data)
                {
                    $isNotified = true;
                    break;
                }
            }

            if (!$isNotified)
            {
                array_unshift($allNotifications, serialize($this));
                filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $targetUser . "/notifications.txt", json_encode($allNotifications));
            }
        }
    }
}

$instantPushNotifications = [];

class NotificationSystemOperator
{
    public array $allNotifications;
    public array $onloadDetectionNotifications; // Cannot be pushed, in current dev state

    public function __construct()
    {
        global $current;
        if (empty($current))
        {
            throw new CurrentSessionInstanceMissingException();
        }

        $this->allNotifications = [];
        if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/notifications.txt"))
        {
            $this->allNotifications = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/notifications.txt"), true);
        }

        foreach ($this->allNotifications as &$notification)
        {
            $notification = unserialize($notification);
        }

        //On-load Detection
        $this->onloadDetectionNotifications = [];

        require_once("Assignments.php");
        if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"))
        {
            $userdoneassignment = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"), true);
        }
        else
        {
            $userdoneassignment = [];
        }
        foreach (getAllAssignments() as $assignment)
        {
            if (!empty($assignment["due"]) && strtotime($assignment["due"]) < strtotime("today") && !in_array((array)$assignment, (array)$userdoneassignment))
            {
                $this->onloadDetectionNotifications[] = new Notification("", "", "Assignments", "missing_assignment", "", NotificationType::info, false);
            }
        }
    }

    public function getAllPushNotificationsDisplay(): string
    {
        $html = "";
        $edited = false;
        foreach ($this->allNotifications as &$notification)
        {
            if ($notification->ifShow() && $notification->banner)
            {
                $html .= $notification->getPushNotificationDisplay();
                $notification->banner = false;
                $edited = true;
            }
        }

        global $instantPushNotifications;
        foreach ((array)$instantPushNotifications as &$notification)
        {
            $html .= $notification->getPushNotificationDisplay();
        }

        if ($edited)
        {
            global $current;
            foreach ($this->allNotifications as &$notification)
            {
                $notification = serialize($notification);
            }
            filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/notifications.txt", json_encode($this->allNotifications));
        }

        return $html;
    }

    public function getPageNotificationCount(): array
    {
        $allNotifications = array_merge($this->allNotifications, $this->onloadDetectionNotifications);
        $pageNotificationCount = [];
        foreach ($allNotifications as &$notification)
        {
            if ($notification->ifShow() && isset($pageNotificationCount[$notification->page]))
            {
                $pageNotificationCount[$notification->page]++;
            }
            else if ($notification->ifShow())
            {
                $pageNotificationCount[$notification->page] = 1;
            }
        }

        return $pageNotificationCount;
    }

    public function getNotificationCountByIDs(string ...$ids): int
    {
        $allNotifications = array_merge($this->allNotifications, $this->onloadDetectionNotifications);
        $count = 0;
        foreach ($allNotifications as $notification)
        {
            if ($notification->ifShow() && in_array($notification->id, $ids))
            {
                $count++;
            }
        }
        return $count;
    }

    public function getNotificationCountByIDnData(string $id, string $data): bool
    {
        $allNotifications = array_merge($this->allNotifications, $this->onloadDetectionNotifications);
        foreach ($allNotifications as $notification)
        {
            if ($notification->ifShow() && $notification->id === $id && $notification->data === $data)
            {
                return true;
            }
        }
        return false;
    }

    public function removeNotificationByIDs(string ...$ids): void
    {
        $allNotifications = $this->allNotifications;
        $edited = false;
        foreach ($allNotifications as $key => &$notification)
        {
            if ($notification->ifShow() && in_array($notification->id, $ids))
            {
                unset($allNotifications[$key]);
                $edited = true;
            }
        }

        if ($edited)
        {
            global $current;
            foreach ($allNotifications as &$notification)
            {
                $notification = serialize($notification);
            }
            filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/notifications.txt", json_encode($allNotifications));
        }

        return;
    }

    public function removeNotificationByIDnData(string $id, string $data): void
    {
        $allNotifications = $this->allNotifications;
        foreach ($allNotifications as $key => &$notification)
        {
            if ($notification->ifShow() && $notification->id === $id && $notification->data === $data)
            {
                unset($allNotifications[$key]);
                global $current;
                foreach ($allNotifications as &$notification)
                {
                    $notification = serialize($notification);
                }
                filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/notifications.txt", json_encode($allNotifications));
                break;
            }
        }

        return;
    }
}
