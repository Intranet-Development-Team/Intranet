<?php
$current = new Session("Resources", "Resources");

function getfilesize($filename)
{
    if (pathInjectionSecure($filename))
    {
        if (is_file("Resources Files/FILES/" . $filename))
        {
            $bytes = filesize("Resources Files/FILES/" . $filename);
            $sizeunits = array('B', 'kB', 'MB', 'GB');
            $lengthfactor = floor((strlen($bytes) - 1) / 3);
            return sprintf("%.2f", $bytes / pow(1024, $lengthfactor)) . " " . @$sizeunits[$lengthfactor];
        }
        else if (is_dir("Resources Files/FILES/" . $filename))
        {
            $num = count(scandir("Resources Files/FILES/" . $filename)) - 2;
            return $num . " " . ($num === 1 ? "item" : "items");
        }
    }
}

if ($current->accessstatus && pathInjectionSecure($_GET["filepath"] ?? ""))
{
    $scanneddir = array_diff(scandir("Resources Files/FILES/" . ($_GET["filepath"] ?? "")), array('..', '.'));
    $finaldisplay = [];
    $result = "";
    foreach ($scanneddir as $item)
    {
        if (is_dir("Resources Files/FILES/" . ($_GET["filepath"] ?? "") . $item))
        {
            $result = '<i class="bi bi-folder-fill" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>' . $item;
            $finaldisplay[] = array($result, getfilesize(($_GET["filepath"] ?? "") . $item));
        }
        else if (is_file("Resources Files/FILES/" . ($_GET["filepath"] ?? "") . $item))
        {
            $extension = pathinfo($item)["extension"];
            $iconts = '<i class="bi bi-file-earmark fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
            switch ($extension)
            {
                case "jpg":
                case "jpeg":
                case "jfif":
                case "pjpeg":
                case "pjp":
                case "apng":
                case "avif":
                case "gif":
                case "png":
                case "svg":
                case "webp":
                case "bmp":
                case "jpeg":
                case "tif":
                case "tiff":
                case "eps":
                case "raw":
                case "heif":
                case "heic":
                    $iconts = '<i class="bi bi-file-earmark-image fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "html":
                case "yaml":
                case "md":
                case "xml":
                case "xhtml":
                case "json":
                    $iconts = '<i class="bi bi-file-earmark-code fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "mp3":
                case "aac":
                case "ogg":
                case "wav":
                case "flac":
                case "ape":
                case "alac":
                    $iconts = '<i class="bi bi-file-earmark-music fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "pdf":
                    $iconts = '<i class="bi bi-file-earmark-pdf fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "webm":
                case "mkv":
                case "flv":
                case "vob":
                case "ogv":
                case "ogg":
                case "avi":
                case "mov":
                case "qt":
                case "wmv":
                case "mpg":
                case "amv":
                case "jpeg":
                case "mp4":
                case "m4p":
                case "m4v":
                case "mpeg":
                    $iconts = '<i class="bi bi-file-earmark-play fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "zip":
                case "rar":
                case "7z":
                case "tar":
                case "gz":
                case "xz":
                    $iconts = '<i class="bi bi-file-earmark-zip fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "doc":
                case "docx":
                case "odt":
                case "rtf":
                case "txt":
                    $iconts = '<i class="bi bi-file-earmark-text fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "xls":
                case "xlsx":
                case "ods":
                    $iconts = '<i class="bi bi-file-earmark-spreadsheet fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
                case "ppt":
                case "pptx":
                case "odp":
                    $iconts = '<i class="bi bi-file-earmark-slides fileitem" style="padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;"></i>';
                    break;
            }
            $result = $iconts . $item;
            $finaldisplay[] = array($result, getfilesize(($_GET["filepath"] ?? "") . $item));
        }
    }
    echo json_encode($finaldisplay);
}
