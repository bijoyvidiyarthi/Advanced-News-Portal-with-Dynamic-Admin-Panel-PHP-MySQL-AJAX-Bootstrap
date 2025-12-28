<?php
session_start();
include "config.php";

//check if the form is submitted 
if (isset($_POST['submit'])) {

  //check if file is uploaded or not
  $file_name = "";
  //initialize errors array
  $errors = array();
  $file_name = $_FILES['logo']['name'];

  if (empty($file_name)) {
    $file_name = $_POST['old_logo'];
  } else {
    $errors = array();

    $file_name = $_FILES['logo']['name'];
    $file_size = $_FILES['logo']['size'];
    $file_tmp = $_FILES['logo']['tmp_name'];
    $file_type = $_FILES['logo']['type'];


    // here the pathinfo function will return an array containing information about the path.then we use PATHINFO_EXTENSION to get only the extension part
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $extensions = array("jpeg", "jpg", "png"); //allowed extensions

    if (in_array($file_ext, $extensions) === false) {
      $errors[] = "This extension file not allowed, Please choose a JPG or PNG file.";
    }

    if ($file_size > 2097152) {
      $errors[] = "File size must be 2mb or lower.";
    }
  }


  $websitename = mysqli_real_escape_string($conn, $_POST["website_name"]);
  $footer_desc = mysqli_real_escape_string($conn, $_POST["footer_desc"]);

  //input Validation
  if (empty($websitename) || empty($footer_desc) || empty($file_name)) {
    $errors[] = "All fields are required.";
  }


  //if there are no errors then proceed the insert query

  if (empty($errors) == true) {
    move_uploaded_file($file_tmp, "images/" . $file_name);
  }
  //if there are errors, store them in session and redirect back to add-post.php
  if (!empty($errors)) {
    $_SESSION['error'] = implode("|||", $errors);
    header("Location: settings.php");
    mysqli_close($conn);
    exit();
  }

  $sql = "UPDATE settings 
          SET websitename='{$websitename}', logo='{$file_name}', footerdesc='{$footer_desc}'";

  $result = mysqli_query($conn, $sql);

  if ($result) {
    $_SESSION['success'] = "Settings updated successfully.";
    header("Location: post.php");
  } else {
    $_SESSION['error'] = "Error updating Settings.";
    header("Location: settings.php");
    exit();
  }
  mysqli_close($conn);

} else {
  $_SESSION['error'] = "Invalid Access.";
  header("Location: settings.php");
  mysqli_close($conn);
}

?>