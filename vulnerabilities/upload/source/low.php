<?php

if( isset( $_POST[ 'Upload' ] ) ) {
    if (!isset($_FILES['uploaded']) || $_FILES['uploaded']['error'] !== UPLOAD_ERR_OK) {
        dvwaSecurityEvent('File Upload', 'vulnerabilities/upload', 'Blocked', 'Upload error or missing file');
        $html .= '<pre>Security control: upload rejected.</pre>';
        return;
    }

    $uploaded_name = (string)$_FILES['uploaded']['name'];
    $uploaded_size = (int)$_FILES['uploaded']['size'];
    $uploaded_tmp  = (string)$_FILES['uploaded']['tmp_name'];
    $ext = strtolower(pathinfo($uploaded_name, PATHINFO_EXTENSION));
    $allowed = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png');

    if (!class_exists('finfo') || !function_exists('getimagesize') || !function_exists('imagecreatefromjpeg') || !function_exists('imagecreatefrompng')) {
        dvwaSecurityEvent('File Upload', 'vulnerabilities/upload', 'Blocked', 'Required image validation extensions are unavailable');
        $html .= '<pre>Security control: server image-validation components are unavailable.</pre>';
        return;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $real_mime = $finfo->file($uploaded_tmp);
    $image_info = @getimagesize($uploaded_tmp); 

    if (!isset($allowed[$ext]) || $real_mime !== $allowed[$ext] || $uploaded_size > 100000 || $image_info === false) {
        dvwaSecurityEvent('File Upload', 'vulnerabilities/upload', 'Blocked', 'Only valid JPEG/PNG images under 100KB are accepted');
        $html .= '<pre>Security control: file rejected. Only valid JPEG/PNG images under 100KB are accepted.</pre>';
        return;
    }

    // Store with an unpredictable name and re-encode the image so executable content is not preserved.
    $target_path = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/';
    $random_name = bin2hex(random_bytes(16)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $target_file = $target_path . $random_name;
    $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16)) . '.' . $ext;

    if ($real_mime === 'image/jpeg') {
        $img = @imagecreatefromjpeg($uploaded_tmp);
        $saved = $img && imagejpeg($img, $temp_file, 90);
    } else {
        $img = @imagecreatefrompng($uploaded_tmp);
        $saved = $img && imagepng($img, $temp_file, 8);
    }
    if (isset($img) && $img) imagedestroy($img);

    $destination = DVWA_WEB_PAGE_TO_ROOT . 'hackable/uploads/' . $random_name;
    if ($saved && rename($temp_file, $destination)) {
        $html .= '<pre>Image successfully uploaded: ' . dvwaSecurityEscape($random_name) . '</pre>';
    } else {
        if (file_exists($temp_file)) @unlink($temp_file);
        $html .= '<pre>Image upload failed.</pre>';
    }
}
?>
