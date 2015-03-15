<?php
  require_once 'app_config.php';

  $self_name = $_SERVER['PHP_SELF'];
  $message = "";
  $error_sorry = "Sorry, there was an error uploading your picture file. ";

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }

  if (isset($_POST['submit'])) {
    $target_dir = "pictures/";
    $target_file = $target_dir . basename($_FILES["uploadFile"]["name"]);
    $uploadOk = 1;
    if (!isset($_COOKIE['user_name']) || !isset($_COOKIE['password'])) {
      $message = $message . $error_sorry;
      $uploadOk = 0;
    } else {
      $user_name = trim($_COOKIE['user_name']);
      $password = trim($_COOKIE['password']);
    }

    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  
// Check whether file is a real or fake image or not an uploaded file
    $check = (getimagesize($_FILES["uploadFile"]["tmp_name"]));
    if (($check == false) || (!is_uploaded_file($_FILES["uploadFile"]["tmp_name"]))) {
      $message = $message . "The given file is not a picture file! ";
      $uploadOk = 0;
    } else {
      $uploadOk = 1;
    }
// Check whether file already exists
//if (file_exists($target_file)) {
//  echo "Sorry, that picture file already exists.";
//  $uploadOk = 0;
//}
  
// Check filesize
    if ($_FILES["uploadFile"]["size"] > 1048576) {
      $message = $message . "Sorry, your picture file is too large. The limit is one megabyte (1048576 bytes). ";
      $uploadOk = 0;
    }
// Allow only certain file types
    $picture_ext = strtolower($imageFileType);
    if($picture_ext != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
      $message = $message . "Sorry, only JPG, JPEG, PNG and GIF files are allowed. ";
      $uploadOk = 0;
    }
// Check whether $uploadOk has been set to 0 by any error
    if ($uploadOk == 0) {
      $message = $message . "Your picture file was not uploaded. ";
// If everything is ok, try to upload
    } else {
      $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
      
      if ($stmt = $mysqli2->prepare("SELECT * FROM " . DATABASE_TABLE . " WHERE (user_name = ?) and 
        (binary password_hash = ?)")) {
        $stmt->bind_param('ss', $user_name, crypt($password,CRYPT_SALT));
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = mysqli_fetch_array($result)) {
          if ($row['picture_ext'] != NULL) {
            $old_filename = $target_dir . $row['id'] . "." . $row['picture_ext'];
// Non-functional:
            @unlink($old_filename);
          }  
          $new_filename = $target_dir . $row['id'] . "." . $picture_ext;

          $stmt->prepare("UPDATE " . DATABASE_TABLE . " SET picture_ext = ? " . 
            "WHERE (user_name = ?) AND (binary password_hash = ?)");
          $stmt->bind_param('sss', $picture_ext, $user_name, crypt($password,CRYPT_SALT));
          $stmt->execute();
        } else {
          $message = $message . $error_sorry;
        }
        $stmt->close();
        $mysqli2->close();
      
  //rename($old_filename, $target_dir . "@DELETE_" . $row['id'] . "." . $row['picture_ext']);
      
        if (!move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $new_filename)) {
          $message = $message . $error_sorry;
        } else {
          $message = "Picture uploaded! To see the new picture, update your page by clicking on Home at " . 
            "the top left (or your browser's Refresh button).";
        }
      } else {
        $message = $message . "Sorry, the connection to the database failed. Your picture file was not uploaded. ";
      }
    }
  } else {
    $message = $message . $error_sorry;
  }
  echo "<!DOCTYPE HTML><HTML><head><script>alert(\"{$message}\"); window.close();</script></head><body></body></html>";
  exit();
  