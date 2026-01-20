<?php
include_once __DIR__ . "/config.php";

// Only administrators are allowed to change settings
if ($_SESSION['user_role'] != 1) {
  $_SESSION['error'] = "You are not allowed this page";
  header("Location: post.php");
  exit();
}

if (isset($_POST['submit'])) {

  $errors = array();
  $file_name = $_FILES['logo']['name'];

  // === 1. Image (Logo) Processing ===
  if (empty($file_name)) {
    // Use existing logo if no new file is uploaded
    $file_name = $_POST['old_logo'];
  } else {
    $file_size = $_FILES['logo']['size'];
    $file_tmp = $_FILES['logo']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $extensions = array("jpeg", "jpg", "png");

    // Validate file extension
    if (in_array($file_ext, $extensions) === false) {
      $errors[] = "This extension file is not allowed. Please choose a JPG or PNG file.";
    }

    // Validate file size (Max 2MB)
    if ($file_size > 2097152) {
      $errors[] = "File size must be 2MB or lower.";
    }

    if (empty($errors)) {
      // Save image with a unique name to prevent cache issues
      $file_name = "logo-" . time() . "-" . $file_name;
      move_uploaded_file($file_tmp, "images/" . $file_name);

      // Delete old logo file (Good practice to save server space)
      $old_logo = $_POST['old_logo'];
      if ($old_logo != "" && file_exists("images/" . $old_logo)) {
        unlink("images/" . $old_logo);
      }
    }
  }

  // === 2. Data Sanitization (Including all columns) ===
  $websitename = mysqli_real_escape_string($conn, $_POST["website_name"]);
  $footer_desc = mysqli_real_escape_string($conn, $_POST["footer_desc"]);
  $site_desc = mysqli_real_escape_string($conn, $_POST["site_desc"]);
  $contact_email = mysqli_real_escape_string($conn, $_POST["contact_email"]);
  $contact_phone = mysqli_real_escape_string($conn, $_POST["contact_phone"]);
  $facebook = mysqli_real_escape_string($conn, $_POST["facebook"]);
  $youtube = mysqli_real_escape_string($conn, $_POST["youtube"]);
  $copyright = mysqli_real_escape_string($conn, $_POST["copyright"]);

  // Input Validation
  if (empty($websitename) || empty($footer_desc)) {
    $errors[] = "Website Name and Footer Description are required.";
  }

  // Redirect if there are any errors
  if (!empty($errors)) {
    $_SESSION['error'] = implode("|||", $errors);
    header("Location: settings.php");
    mysqli_close($conn);
    exit();
  }

  // === 3. Update Query (New columns integrated) ===
  $sql = "UPDATE settings 
            SET websitename = '{$websitename}', 
                logo = '{$file_name}', 
                footerdesc = '{$footer_desc}',
                site_desc = '{$site_desc}',
                contact_email = '{$contact_email}',
                contact_phone = '{$contact_phone}',
                facebook_page = '{$facebook}',
                youtube_url = '{$youtube}',
                copyright_text = '{$copyright}'";

  $result = mysqli_query($conn, $sql);

  if ($result) {
    $_SESSION['success'] = "Settings updated successfully.";
    header("Location: settings.php");
  } else {
    $_SESSION['error'] = "Error updating Settings: " . mysqli_error($conn);
    header("Location: settings.php");
  }

  mysqli_close($conn);

} else {
  // Prevent direct access to the script
  $_SESSION['error'] = "Invalid Access.";
  header("Location: settings.php");
  exit();
}
?>