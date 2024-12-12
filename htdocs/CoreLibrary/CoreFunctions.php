<?php
ob_start();

// Site Constants

define("SITE_NAME", "Intranet");
define("SITE_DOMAIN", "localhost");

// Error Handling

set_error_handler("errorHandler", E_ALL);
register_shutdown_function("shutdownHandler");

function shutdownHandler(): void
{
    $error = error_get_last();
    if ($error)
    {
        errorHandler($error['type'], $error["message"], $error["file"], $error["line"]);
    }
}

function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
{
    $errstr = nl2br(htmlspecialchars($errstr));
    $errmsg = "";
    $iserror = false;

    switch ($errno)
    {
        case E_CORE_ERROR:
            ob_end_clean();
            $id = uniqid("S-F-"); // Startup Fatal Event
            $errmsg = "<div class=\"alert alert-danger\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Startup Error</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            http_response_code(500);
            echo '<head><meta charset="utf-8"><noscript><meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/JSdisabled/"></noscript><link rel="icon" href="/Complements/img/icon.png"><title>500 Internal Server Error | ' . SITE_NAME . ' </title><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"> </head><body class="vh-100 d-flex align-items-center text-center"><main class="w-100"><h1 style="font-size:10em;font-family: monospace;">500</h1><h2><b>Internal Server Error</b></h2><br><h2>An error has occurred. Please report this to our developers and give them the error ID below.</h2><br><br><h6>Error ID: <b>' . $id . '</b></h6></main></body>';
            $iserror = true;
            break;

        case E_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            ob_end_clean();
            $id = uniqid("RT-F-"); // Run-time Fatal Event
            $errmsg = "<div class=\"alert alert-danger\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Runtime Error</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            http_response_code(500);
            echo '<head><meta charset="utf-8"><noscript><meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/JSdisabled/"></noscript><link rel="icon" href="/Complements/img/icon.png"><title>500 Internal Server Error | ' . SITE_NAME . ' </title><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"> </head><body class="vh-100 d-flex align-items-center text-center"><main class="w-100"><h1 style="font-size:10em;font-family: monospace;">500</h1><h2><b>Internal Server Error</b></h2><br><h2>An error has occurred. Please report this to our developers and give them the error ID below.</h2><br><br><h6>Error ID: <b>' . $id . '</b></h6></main></body>';
            $iserror = true;
            break;

        case E_COMPILE_ERROR:
        case E_PARSE:
            ob_end_clean();
            $id = uniqid("CT-F-"); // Compile-time Fatal Event
            $errmsg = "<div class=\"alert alert-danger\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Compiler Error</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            http_response_code(500);
            echo '<head><meta charset="utf-8"><noscript><meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/JSdisabled/"></noscript><link rel="icon" href="/Complements/img/icon.png"><title>500 Internal Server Error | ' . SITE_NAME . ' </title><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"> </head><body class="vh-100 d-flex align-items-center text-center"><main class="w-100"><h1 style="font-size:10em;font-family: monospace;">500</h1><h2><b>Internal Server Error</b></h2><br><h2>An error has occurred. Please report this to our developers and give them the error ID below.</h2><br><br><h6>Error ID: <b>' . $id . '</b></h6></main></body>';
            $iserror = true;
            break;

        case E_CORE_WARNING:
            $id = uniqid("S-NF-"); //Startup Non-fatal Event
            $errmsg = "<div class=\"alert alert-warning\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Startup Warning</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;

        case E_WARNING:
        case E_USER_WARNING:
            $id = uniqid("RT-NF-"); //Run-time Non-fatal Event
            $errmsg = "<div class=\"alert alert-warning\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Runtime Warning</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;

        case E_COMPILE_WARNING:
            $id = uniqid("CT-NF-"); //Compile-time Non-fatal Event
            $errmsg = "<div class=\"alert alert-warning\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Compiler Warning</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            $id = uniqid("RT-PEC-"); //Run-time Potentially Error-causing Event
            $errmsg = "<div class=\"alert alert-info\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Runtime Notice</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;

        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $id = uniqid("RT-PEC-"); //Run-time Potentially Error-causing Event
            $errmsg = "<div class=\"alert alert-secondary\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">Deprecated Code Notice</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;

        default:
            $id = uniqid("X-"); // Unidentified Event
            $errmsg = "<div class=\"alert alert-light\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">???</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br><b>$errstr</b><p style=\"margin:.5em 0em 0em 0em;\">File: <b>$errfile</b><br>Line: <b>$errline</b><br>Error ID: <b>$id</b></p></div>";
            break;
    }
    filewrite($_SERVER["DOCUMENT_ROOT"] . "/Dev/errorlog.txt", $errmsg, "beginning");
    if ($iserror)
    {
        exit();
    }
    return true;
}

// Root Constants

define("SUBJECTS", ["cores" => ["CHIN", "ENG", "MATH", "CS"], "electives" => ["x1" => ["X1 BAFS (MGT)", "X1 BIO", "X1 CHEM", "X1 E&RE", "X1 GEOG", "X1 ICT", "X1 PHY"], "x2" => ["X2 BAFS (ACC)", "X2 CHEM", "X2 CLIT", "X2 ECON", "X2 HIST", "X2 PE", "X2 PHY"], "x3" => ["X3 BIO", "X3 CHEM", "X3 CHIS", "X3 ECON", "X3 PHY", "X3 VA"]], "others" => ["Other"]]);
define("SUBJECT_LIST", array_merge(SUBJECTS["cores"], SUBJECTS["electives"]["x1"], SUBJECTS["electives"]["x2"], SUBJECTS["electives"]["x3"], SUBJECTS["others"]));

$allUsers = [];
foreach (new DirectoryIterator($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/") as $user)
{
    if (!$user->isDot() && $user->isDir())
    {
        $allUsers[] = $user->getBasename();
    }
}
define("USER_LIST", $allUsers);

define("ROLE_LIST", ["student", "teacher", "moderator", "developer"]);

// Root Functions

function filewrite(string $filename, string $contents, string $append = ""): bool
{
    $originalcontents = "";
    if (is_file($filename))
    {
        $originalcontents = fileread($filename);
    }
    $file = fopen($filename, "w");
    if (flock($file, LOCK_EX | LOCK_NB))
    {
        if ($append === "end")
        {
            fwrite($file, $originalcontents . $contents);
        }
        else if ($append === "beginning")
        {
            fwrite($file, $contents . $originalcontents);
        }
        else
        {
            fwrite($file, $contents);
        }
        fflush($file);
        fclose($file);
        return true;
    }
    else
    {
        for ($trials = 1; $trials <= 100; $trials++)
        {
            usleep(100000);
            if (flock($file, LOCK_EX | LOCK_NB))
            {
                if ($append === "end")
                {
                    fwrite($file, $originalcontents . $contents);
                }
                else if ($append === "beginning")
                {
                    fwrite($file, $contents . $originalcontents);
                }
                else
                {
                    fwrite($file, $contents);
                }
                fflush($file);
                fclose($file);
                return true;
            }
        }
        fclose($file);
        return false;
    }
}

function fileread(string $filename): bool|string
{
    if (is_file($filename))
    {
        clearstatcache();
        $filesize = filesize($filename);
        clearstatcache();
        $file = fopen($filename, "r");
        if (flock($file, LOCK_EX | LOCK_NB))
        {
            $fcontents = ($filesize > 0 ? fread($file, $filesize) : "");
            fclose($file);
            return $fcontents;
        }
        else
        {
            for ($trials = 1; $trials <= 100; $trials++)
            {
                usleep(100000);
                if (flock($file, LOCK_EX | LOCK_NB))
                {
                    $fcontents = ($filesize > 0 ? fread($file, $filesize) : "");
                    fclose($file);
                    return $fcontents;
                }
            }
            fclose($file);
            return false;
        }
    }
    else
    {
        $stacktraceoutput = "";
        $length = 0;
        foreach (debug_backtrace() as $index => $content)
        {
            $stacktraceoutput .= "#$index " . $content["file"] . "(" . $content["line"] . ")" . (!empty($content["function"]) ? ": " . $content["function"] . "(" . implode(",", $content["args"]) . ")" : "") . PHP_EOL;
            ++$length;
        }
        $stacktraceoutput .= "#$length {main}" . PHP_EOL . "thrown";

        trigger_error("The file \"" . $filename . "\" doesn't exist" . PHP_EOL . "Stack trace:" . PHP_EOL . $stacktraceoutput, E_USER_WARNING);
        return false;
    }
}

function random_str(): string
{
    $return = "";
    $max = strlen("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()_+-={}[]:\";'<>?,./|\\") - 1;
    for ($i = 0; $i < 128; ++$i)
    {
        $return .= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()_+-={}[]:\";'<>?,./|\\"[random_int(0, $max)];
    }
    return $return;
}

function isURL(string $url): bool
{
    if (preg_match('/^https?:\\/\\//', $url))
    {
        return true;
    }
    return false;
}

function pathInjectionSecure(string $str): bool
{
    if (str_contains($str, ".."))
    {
        return false;
    }
    return true;
}

// Root Exceptions

class UnknownUsernameException extends Exception
{
}
class UnknownRoleException extends Exception
{
}
class CurrentSessionInstanceMissingException extends Exception
{
}

// Root Classes

class User
{
    public string $username = "";
    public string $usernamefordisplay = "";
    public array $role = [];
    public string $roleicon = "";
    public string $rolefordisplay = "";

    public function __construct(string $username)
    {
        if (pathInjectionSecure($username) && is_dir($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $username))
        {
            $this->username = $username;

            $rolesaved = (array)json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $username . "/role.txt"), true);
            if (array_intersect($rolesaved, ROLE_LIST) === $rolesaved)
            {
                $this->role = $rolesaved;
                foreach ($rolesaved as $role)
                {
                    switch ($role)
                    {
                        case "student":
                            $this->rolefordisplay .= '<span class="ps-2 pe-2">Student</span>';
                            break;

                        case "teacher":
                            $this->rolefordisplay .= '<span class="ps-2 pe-2">Teacher</span>';
                            break;

                        case "moderator":
                            $this->rolefordisplay .= '<span class="ps-2 pe-2"><i class="bi bi-hexagon-fill"></i> Moderator</span>';
                            $this->roleicon .= '<i class="bi bi-hexagon-fill ps-1 pe-1"></i>';
                            break;

                        case "developer":
                            $this->rolefordisplay .= '<span class="ps-2 pe-2"><i class="bi bi-code-slash"></i> Developer</span>';
                            $this->roleicon .= '<i class="bi bi-code-slash ps-1 pe-1"></i>';
                            break;
                    }
                }
                $this->usernamefordisplay = $this->username . $this->roleicon;
            }
            else
            {
                throw new UnknownRoleException((string)$rolesaved);
            }
        }
        else
        {
            throw new UnknownUsernameException($username);
        }
    }

    public function hasRole(array $targetroles): bool // Has any of the roles
    {
        foreach ($this->role as $role)
        {
            if (in_array($role, $targetroles))
            {
                return true;
            }
        }
        return false;
    }

    public function getpfp(int $size = 75): string
    {
        $pfp = imagecreatefromstring(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/pfp.txt"));
        $pfp = imagescale($pfp, $size, $size);
        return "data:image/png;base64," . base64_encode($pfp);
    }

    public function setpfp(string $newcontent): void
    {
        filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/pfp.txt", $newcontent);
    }

    public function setpw(string $newpw): void
    {
        $hash = hash("sha3-512", $newpw);
        filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/password.txt", $hash);
    }

    public function validatepw(string $pw): bool
    {
        $hash = hash("sha3-512", $pw);
        if ($hash === fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/password.txt"))
        {
            return true;
        }
        return false;
    }

    public function getBirthday(): int
    {
        return (int)fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/birthday.txt");
    }

    public function setBirthday(int $date): void
    {
        filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/birthday.txt", $date);
    }
}

class Session extends User
{
    public bool $loginstatus = false;
    public bool $accessstatus = false;

    public string $loginstatusmsg = "";
    public string $accessstatusmsg = "";

    public string $hashedloginid = "";

    private array $sitepages = ["nav" => ["Home" => "", "Assignments" => "Assignments", "Calendar" => "Calendar", "Blog" => "Blog", "Chat" => "Chat", "Mail" => "Mail", "Resources" => "Resources", "Support" => "Support"], "navright" => ["Account Settings" => "Account"], "footer" => ["Conduct" => "Conduct", "Copyright" => "Copyright", "Privacy" => "Privacy"]];
    private string $currentpage = "";
    private string $currenturl = "";

    public function __construct(string $currentpage = "", string $currenturl = "", array $allowedroles = ROLE_LIST, bool $autoredirect = true)
    {
        require_once("Notification.php");

        $this->currentpage = $currentpage;
        $this->currenturl = $currenturl;

        if (!empty($_COOKIE["user"]) && !empty($_COOKIE["id"]) && !empty($_COOKIE["verification"]))
        {
            $temphashedid = hash("sha3-512", $_COOKIE["id"]);

            if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $_COOKIE["user"] . "/Logins/" . $temphashedid . ".txt"))
            {
                $logindata = json_decode(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $_COOKIE["user"] . "/Logins/" . $temphashedid . ".txt"), true);

                if ($logindata["verification"] === hash("sha3-512", $_COOKIE["verification"]) && $logindata["exptime"] > time() && $logindata["lastactive"] + 60 * 60 * 24 * 14 > time())
                {
                    ##### Valid session confirmed #####

                    $this->hashedloginid = $temphashedid;
                    parent::__construct($_COOKIE["user"]);

                    // Update session info
                    $logindata["lastactive"] = time();

                    if (!in_array($_SERVER["REMOTE_ADDR"], $logindata["ipaddress"]))
                    {
                        $logindata["ipaddress"][] = $_SERVER["REMOTE_ADDR"];
                    }

                    if ($logindata["lastidupdate"] + 60 * 60 * 24 < time())
                    {
                        $logindata["lastidupdate"] = time();

                        // Update session verification
                        $randomVerification = random_str();

                        $logindata["verification"] = hash("sha3-512", $randomVerification);

                        setcookie("verification", $randomVerification, [
                            'expires' => time() + 60 * 60 * 24 * 365,
                            'path' => '/',
                            'domain' => SITE_DOMAIN,
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                        $_COOKIE["verification"] = $randomVerification;
                    }

                    filewrite($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/Logins/" . $this->hashedloginid . ".txt", json_encode($logindata));

                    ##### Ending session operations, starting to check whether to user is able to access the site. #####

                    if ((!is_file($_SERVER["DOCUMENT_ROOT"] . "/Dev/maintaining.txt")) || $this->hasRole(["developer"]))
                    {
                        if (!is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/suspended.txt") || strtotime(fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/suspended.txt")) < time())
                        {
                            ##### Sitewide access authorized #####
                            $this->loginstatus = true;

                            // Setting role-limited pages
                            if ($this->hasRole(["moderator"]))
                            {
                                $this->sitepages["navright"]["Mod Console"] = "Mod";
                            }
                            if ($this->hasRole(["developer"]))
                            {
                                $this->sitepages["navright"]["Dev Console"] = "Dev";
                            }

                            ##### Ending sitewide access operations, starting to check whether to user is able to access the page. #####

                            if ($this->hasRole($allowedroles))
                            {

                                ##### Page access and site access are both authorized #####

                                $this->accessstatus = true;
                            }
                            else
                            {
                                $this->accessstatusmsg = '<div class="vh-100 d-flex align-items-center text-center"><div class="w-100"><h1>Access Denied</h1><h2>Your role has no permission to access this page.</h2></div></div>';
                            }
                        }
                        else
                        {
                            $this->loginstatusmsg = "Your account is suspended.";

                            if ($autoredirect)
                            {
                                header("Location: https://" . SITE_DOMAIN . "/Login" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                                exit();
                            }
                        }
                    }
                    else
                    {
                        $this->loginstatusmsg = "Maintenance is in progress.";

                        if ($autoredirect)
                        {
                            header("Location: https://" . SITE_DOMAIN . "/Login" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                            exit();
                        }
                    }
                }
                else
                {
                    $this->loginstatusmsg = "Session expired. Please retry login.";

                    unlink($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $_COOKIE["user"] . "/Logins/" . $temphashedid . ".txt");

                    if ($autoredirect)
                    {
                        header("Location: https://" . SITE_DOMAIN . "/Login" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                        exit();
                    }
                }
            }
            else
            {
                $this->loginstatusmsg = "Session expired. Please retry login.";

                if ($autoredirect)
                {
                    header("Location: https://" . SITE_DOMAIN . "/Login" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                    exit();
                }
            }
        }
        else
        {
            if ($autoredirect)
            {
                header("Location: https://" . SITE_DOMAIN . "/Login" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                exit();
            }
        }

        // First-time user setting pages
        if ($this->loginstatus)
        {
            if (!is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/firstlogin.txt"))
            {
                $this->accessstatus = false;
                $this->accessstatusmsg = '<div class="vh-100 d-flex align-items-center text-center"><div class="w-100"><h1>Password Changing Required</h1><h2>You have to change your password before accessing Intranet.</h2></div></div>';
                if ($this->currentpage !== "Change Password" && $this->currentpage !== "Login")
                {
                    header("Location: https://" . SITE_DOMAIN . "/Login/ChangePassword" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                    exit();
                }
            }
            else if (!is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/birthday.txt"))
            {
                $this->accessstatus = false;
                $this->accessstatusmsg = '<div class="vh-100 d-flex align-items-center text-center"><div class="w-100"><h1>Birthday Setting Required</h1><h2>You have to set your birthday before accessing Intranet.</h2></div></div>';
                if ($this->currentpage !== "Set Birthday" && $this->currentpage !== "Login")
                {
                    header("Location: https://" . SITE_DOMAIN . "/Login/SetBirthday" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                    exit();
                }
            }
            else if (!is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/electives.txt"))
            {
                $this->accessstatus = false;
                $this->accessstatusmsg = '<div class="vh-100 d-flex align-items-center text-center"><div class="w-100"><h1>Electives Setting Required</h1><h2>You have to set your electives before accessing Intranet.</h2></div></div>';
                if ($this->currentpage !== "Set Electives" && $this->currentpage !== "Login")
                {
                    header("Location: https://" . SITE_DOMAIN . "/Login/SetElectives" . (!empty($this->currenturl . $_SERVER["QUERY_STRING"]) ? "?redirect=" . urlencode($this->currenturl) . (!empty($_SERVER["QUERY_STRING"]) ? urlencode("?" . $_SERVER["QUERY_STRING"]) : "") : ""));
                    exit();
                }
            }
        }
    }

    public function getNavBar(): string // With Notification Display
    {
        $displaybar = '<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" id="nav"><div class="container-fluid"><a class="navbar-brand" href="">' . SITE_NAME . '</a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="navbarCollapse"><ul class="navbar-nav me-auto mb-2 mb-md-0">';

        if ($this->loginstatus)
        {
            $notificationoperator = new NotificationSystemOperator();
            $pageNotificationCount = $notificationoperator->getPageNotificationCount();
        }

        foreach ($this->sitepages["nav"] as $name => $link)
        {
            $badge = "";

            if ($this->loginstatus)
            {
                if (isset($pageNotificationCount[$name]) && $this->currentpage !== $name)
                {
                    $badge = '<span class="badge bg-danger rounded-pill ms-2 align-self-center" style="font-size:.6rem;">' . $pageNotificationCount[$name] . '</span>';
                }
            }

            $displaybar .= '<li class="nav-item"><a class="nav-link d-inline-flex' . ($this->currentpage === $name ? ' active" href="#"' : '" href="/' . $link . '"') . '>' . $name  . $badge . '</a></li>';
        }

        if ($this->loginstatus)
        {
            $displaybar .= '</ul><ul class="navbar-nav nav justify-content-end"><div class="d-flex justify-content-around align-items-center">';

            foreach ($this->sitepages["navright"] as $name => $link)
            {
                switch ($name)
                {
                    case "Account Settings":
                        $displayname = '<img src="' . $this->getpfp() . '" style="width:2rem;vertical-align: text-bottom;" class="rounded-circle pfp">';
                        break;
                    case "Mod Console":
                        $displayname = '<i class="bi bi-hexagon-fill d-block"></i>';
                        break;
                    case "Dev Console":
                        $displayname = '<i class="bi bi-code-slash d-block"></i>';
                        break;
                    default:
                        $displayname = $name;
                }

                $badge = "";
                if (isset($pageNotificationCount[$name]) && $this->currentpage !== $name)
                {
                    $badge = '<span class="position-absolute translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;top:10%;left:90%;">' . $pageNotificationCount[$name] . '</span>';
                }

                $displaybar .= '<a class="nav-link d-inline-block ms-1 pt-0 pb-0 rounded-3 ps-2 pe-2' . ($this->currentpage === $name ? " active" : "") . '" href="' . ($this->currentpage === $name ? "#" : "/" . $link) . '" style="font-size: 1.5rem;"><div class="position-relative">' . $displayname . $badge . '</div></a>';
            }

            $displaybar .= '</div></ul></div></div></nav>';
        }
        else
        {
            $displaybar .= '</ul><ul class="navbar-nav mb-auto mt-auto"><li class="nav-item"><a class="nav-link active" href="https://' . SITE_DOMAIN . '/Login/">Log in</a></li></ul>';
        }

        if ($this->loginstatus)
        {
            $displaybar .= '<div style="position:fixed;top:4.5em;right:1em;z-index:999;width:25em;" id="notificationcontainer">' . $notificationoperator->getAllPushNotificationsDisplay() . '</div>';
        }

        return $displaybar;
    }

    public function getHtmlHead(): string
    {
        return '<head><meta name="viewport" content="width=device-width, initial-scale=1"><noscript>
        <meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/jsdisabled.html"></noscript><link rel="stylesheet" href="/Complements/core.css"><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="/Complements/Bootstrap/icons/font/bootstrap-icons.min.css"><script type="text/javascript" src="/Complements/Bootstrap/js/bootstrap.bundle.min.js"></script><script type="text/javascript" src="/Complements/jQuery/jquery.js"></script><script type="text/javascript" src="/Complements/core.js"></script><link rel="icon" href="/Complements/img/icon.png"><title>' . $this->currentpage . ' | ' . SITE_NAME . '</title></head>';
    }

    public function getFooter(): string
    {
        $links = [];

        foreach ($this->sitepages["footer"] as $name => $link)
        {
            $links[] = '<a href="' . ($this->currentpage === $name ? "#" : "/" . $link) . '">' . $name . '</a>';
        }

        return '<footer class="mb-3"><hr><p>' . implode(" &middot; ", $links) . '</p><p><small style="display:block;">Site design &copy; Intranet Development Team. All rights reserved.</small></p></footer>';
    }

    public function logOut(): void
    {
        if (is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/Logins/" . $this->hashedloginid . ".txt"))
        {
            unlink($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $this->username . "/Logins/" .  $this->hashedloginid . ".txt");
        }
        setcookie("user", "", time() - 1000, "/", SITE_DOMAIN, true, true);
        setcookie("id", "", time() - 1000, "/", SITE_DOMAIN, true, true);
        setcookie("verification", "", time() - 1000, "/", SITE_DOMAIN, true, true);

        header("Location: https://" . SITE_DOMAIN . "/Login/");
        ob_end_clean();
        exit();
    }
}
