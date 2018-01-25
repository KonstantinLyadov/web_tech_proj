<?php

require_once './config.php';

// db
$db_conn = new mysqli($db_host, $db_usr, $db_pwd, $db_base, $db_port);
if ($db_conn->connect_error) {
    die('db Not connected /n' . $db_conn->connect_error);
}

function dbQuery($query) {
    global $db_conn;
    $result = $db_conn->query($query);
    if (!$result) {
        die($db_conn->connect_error);
    }
    return $result;
}

function getLastId() {
    global $db_conn;
    return $db_conn->insert_id;
}

function mysql_entities_fix_string(/* $connection, */ $string) {
    return htmlentities(mysql_fix_string(/* $db_conn, */ $string));
}

function mysql_fix_string(/* $connection, */ $string) {
    if (get_magic_quotes_gpc()) {
        $string = stripslashes($string);
    }
    global $db_conn;
    return $db_conn->real_escape_string($string);
}

// список файлов
function filesGetList($search_str) {
    $fileList;
    $dir = getcwd();
    global $uplDir;
    $dir .= $uplDir;
    if (is_dir($dir)) {
        $i = 0;
        if (($dh = opendir($dir)) !== false) {
            $fileList = '<ul id="info" class="info_b">';
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || $file == 'index.php')
                    continue;
                if ($search_str && !substr_count($file, $search_str))
                    continue;
                $href = $dir . $file;
                $fileList .= "<li>[" . ++$i . "]$file<br/>"
                        . "<a href=\"?getfile=" . ($file) . "\">Скачать</a>&nbsp;"
                        //. "<a href=\"?delfile=" . ($file) . "\">Удалить</a>"
                        //. "<a href=\"javascript:ConfirmDel('" . ($file) . "')\">Удалить</a>"
                        . "<a href=\"?delfile=" . ($file) . "\" onclick=\"return ConfirmDel('" . ($file) . "')\">Удалить</a>"
                        . "</li>";
            }
            $fileList .= '<li><a class="cupllnk" href ="#">Загрузить ещё файл</a></li>';
            $fileList .= '</ul>';

        }
        closedir($dh);

        if ($i < 1) {
            $fileList .= '<p>Нет файлов для отображения</p>';
        }
    }
    if ($search_str)
        $fileList .= '<p><a href ="?page=files">Показать все</a></p>';
    return $fileList;
}

function filesReadWeather() {
    $dir = getcwd() . '\\parser\\iwx\\';
    $lines = file($dir . 'weather2.txt');
    if ($lines) {
        foreach ($lines as $line_no => $line){
            $result[$line_no] = mb_convert_encoding($line, 'utf-8', 'windows-1251');
        }
    } else {
        $result = FALSE;
    }
    return $result;
}

function filesDownload($fn) {
    $dir = getcwd();
    global $uplDir;
    $dir .= $uplDir;
    if (is_dir($dir) && is_file($dir . $fn)) {
        header("Content-Type: application/force-download; name=\"" . $fn . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($dir . $fn));
        header("Content-Disposition: attachment; filename=\"" . $fn . "\"");
        readfile($dir . $fn);
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        return TRUE;
    } else return FALSE;
}

function filesRemove($fn) {
    $dir = getcwd();
    global $uplDir;
    $dir .= $uplDir;
    if (is_dir($dir) && is_file($dir . $fn)) {
        unlink($dir . $fn);
        removeFileInfo($fn);
        return TRUE;
    } else return FALSE;
}

function filesUpload($fn, $fname) {

    $result = '';
    if (is_uploaded_file($fn)) {//Проверяем, загружен ли файл

        global $uplDir;         // будем сохранять загружаемые файлы в эту директорию; имя файла оставим неизменным
        $uploaddir = getcwd() . $uplDir;
        if (!file_exists($uploaddir))
            mkdir($uploaddir);
        $destination = $uploaddir . $fname;

        // перемещаем файл из временной папки в выбранную директорию для хранения
        if (move_uploaded_file($fn, $destination)) {
            //print "Файл успешно загружен <br>";
            //$result = false;
            insertFileInfo($fn, $_FILES['myfile']['size']);
        } else {
            $result = "Произошла ошибка при загрузке файла. Некоторая отладочная информация:<br>" .
                    print_r($_FILES);
        }

        header("Location:index.php?page=files");
    }
}

function insertFileInfo($fn, $size){
    global $userId;
    $query = "INSERT INTO t_files(user_id,name_stored,name_original,size) VALUES('$userId', '$fn', '$fn', '$size');";
    $result = dbQuery($query);
    return $fileInserted = getLastId();
    
}

function removeFileInfo($fn){
    global $userId;
    $query = "DELETE FROM t_files WHERE userid='$userId' AND name_stored='$fn';";
    $result = dbQuery($query);
    return ($result->num_rows>0);
}

?>