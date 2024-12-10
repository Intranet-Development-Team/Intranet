<?php
$current = new Session("Resources", "EditResources");

if ($current->accessstatus)
{
    if (isset($_GET["filepath"]) && pathInjectionSecure($_GET["filepath"]) && pathInjectionSecure($_POST["filename"] ?? ""))
    {
        $targetfilepath = "../Resources/Resources Files/FILES/" . $_GET["filepath"] . $_POST["filename"];
        if (isset($_GET["rename"]) && file_exists("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $_POST["filename"]))
        {
            $_POST["newfilename"] = trim($_POST["newfilename"]);
            if (preg_match('/[^a-zA-Z0-9_ \(\).]/', $_POST["newfilename"]) === 0 && strlen($_POST["newfilename"]) <= 64 && $_POST["newfilename"] != basename($_POST["filename"]))
            {
                $newfilename = $_POST["newfilename"];
                $extension = pathinfo($_POST["filename"])["extension"];
                $counter = 1;
                while (file_exists("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $newfilename . (isset($extension) ? "." . $extension : "")))
                {
                    $newfilename = $_POST["newfilename"] . " ($counter)";
                    ++$counter;
                }
                if (is_dir($targetfilepath))
                {
                    rename("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $_POST["filename"], "../Resources/Resources Files/FILES/" . $_GET["filepath"] . $newfilename);
                }
                else if (is_file($targetfilepath))
                {
                    rename("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $_POST["filename"], "../Resources/Resources Files/FILES/" . $_GET["filepath"] . $newfilename . (isset($extension) ? "." . $extension : ""));
                }
            }
            else
            {
                echo "File renaming failed. Please try again.";
            }
        }
        else if (isset($_GET["delete"]) && file_exists("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $_POST["filename"]))
        {
            if (is_dir($targetfilepath))
            {
                $it = new RecursiveDirectoryIterator($targetfilepath, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file)
                {
                    if ($file->isDir())
                    {
                        rmdir($file->getRealPath());
                    }
                    else
                    {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($targetfilepath);
            }
            else if (is_file($targetfilepath))
            {
                unlink($targetfilepath);
            }
        }
        else if (isset($_GET["newfolder"]) && is_dir("../Resources/Resources Files/FILES/" . $_GET["filepath"]))
        {
            if (is_dir($targetfilepath))
            {
                $_POST["newfoldername"] = trim(urldecode($_POST["newfoldername"]));
                $newfoldername = $_POST["newfoldername"];
                $counter = 1;
                while (is_dir("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $newfoldername))
                {
                    $newfoldername = $_POST["newfoldername"] . " ($counter)";
                    ++$counter;
                }
                if (preg_match("/[^a-zA-Z0-9_ \(\).]/", $_POST["newfoldername"]) === 0 && strlen($_POST["newfoldername"]) <= 64)
                {
                    mkdir("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $newfoldername, 0700);
                }
                else
                {
                    echo "Folder creation failed. Please try again.";
                }
            }
            else
            {
                echo "Folder creation failed. Please try again.";
            }
        }
        else if (isset($_GET["newfileupload"]) && is_dir("../Resources/Resources Files/FILES/" . $_GET["filepath"]))
        {
            if (is_uploaded_file($_FILES['file']['tmp_name']) && preg_match('/[^a-zA-Z0-9_ \(\).]/', $_FILES["file"]["name"]) === 0 && strlen($_FILES["file"]["name"]) <= 64 && $_FILES["file"]["size"] <= 10485760)
            {
                $allowedtypes = ["jpg", "jpeg", "jfif", "pjpeg", "pjp", "apng", "avif", "gif", "png", "svg", "webp", "bmp", "tif", "tiff", "eps", "raw", "heif", "heic", "html", "yaml", "md", "xml", "xhtml", "json", "mp3", "aac", "ogg", "wav", "flac", "ape", "alac", "pdf", "webm", "mkv", "flv", "vob", "ogv", "ogg", "avi", "mov", "qt", "wmv", "mpg", "amv", "mp4", "m4p", "m4v", "mpeg", "zip", "rar", "7z", "tar", "gz", "xz", "doc", "docx", "odt", "rtf", "txt", "xls", "xlsx", "ods", "xls", "xlsx", "ods", "ppt", "pptx", "odp"];
                if (in_array(pathinfo($_FILES["file"]["name"])["extension"], $allowedtypes))
                {
                    $originalfilename = pathinfo(basename($_FILES["file"]["name"]))["filename"];
                    $fileextension = pathinfo($_FILES["file"]["name"])["extension"];
                    $filename = $originalfilename;
                    $counter = 1;
                    while (is_file("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $filename . "." . $fileextension))
                    {
                        $filename = $originalfilename . " ($counter)";
                        ++$counter;
                    }
                    move_uploaded_file($_FILES["file"]["tmp_name"], "../Resources/Resources Files/FILES/" . $_GET["filepath"] . $filename . "." . $fileextension);
                    chmod("../Resources/Resources Files/FILES/" . $_GET["filepath"] . $filename . "." . $fileextension, 0600);
                }
                else
                {
                    echo "File upload failed. The file type is not allowed. Please try again.";
                }
            }
            else
            {
                echo "File upload failed. Please try again.";
            }
        }
    }
}
