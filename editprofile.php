<?php
  require_once('startsession.php');
  $page_title = 'Редактирование профиля';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

  // Make sure the user is logged in before going any further.
  if (!isset($_SESSION['user_id'])) {
    echo '<p class="login">Пожалуйста, <a href="login.php">войдите, </a>чтобы получить доступ к этой странице.</p>';
    exit();
  }
  else {
    echo('<p class="login">Вы вошли как ' . $_SESSION['login'] . '. <a href="logout.php">Выйти</a>.</p>');
  }

  // Connect to the database
StartDB();

  if (isset($_POST['submit'])) {
    // Grab the profile data from the POST
    $login = mysqli_real_escape_string($dbc, trim($_POST['login']));
    $username = mysqli_real_escape_string($dbc, trim($_POST['username']));
    $usersurname = mysqli_real_escape_string($dbc, trim($_POST['usersurname']));
    $user_email = mysqli_real_escape_string($dbc, trim($_POST['user_email']));
    $old_password = mysqli_real_escape_string($dbc, trim($_POST['old_password']));
    $new_password1 = mysqli_real_escape_string($dbc, trim($_POST['new_password1']));
    $new_password2 = mysqli_real_escape_string($dbc, trim($_POST['new_password2']));
    $old_picture = mysqli_real_escape_string($dbc, trim($_POST['old_picture']));
    $error = false;
    $new_picture = mysqli_real_escape_string($dbc, trim($_FILES['new_picture']['name']));
        
    // Validate and move the uploaded picture file, if necessary
    if (!empty($new_picture)) {

	list($new_picture_width, $new_picture_height) = getimagesize($_FILES['new_picture']['tmp_name']);
    $new_picture_type = $_FILES['new_picture']['type'];
    $new_picture_size = $_FILES['new_picture']['size']; 

      if ((($new_picture_type == 'image/gif') || ($new_picture_type == 'image/jpeg') || ($new_picture_type == 'image/pjpeg') ||
        ($new_picture_type == 'image/png')) && ($new_picture_size > 0) && ($new_picture_size <= HR_MAXFILESIZE) &&
        ($new_picture_width <= HR_MAXIMGWIDTH) && ($new_picture_height <= HR_MAXIMGHEIGHT)) {
        if ($_FILES['new_picture']['error'] == 0) {          // Move the file to the target upload folder
          $target = HR_UPLOADPATH . basename($new_picture);
          if (move_uploaded_file($_FILES['new_picture']['tmp_name'], $target)) {
            // The new picture file move was successful, now make sure any old picture is deleted
            if (!empty($old_picture) && ($old_picture != $new_picture)) {
              @unlink(HR_UPLOADPATH . $old_picture);
            }
          }
          else {
            // The new picture file move failed, so delete the temporary file and set the error flag
            @unlink($_FILES['new_picture']['tmp_name']);
            $error = true;
            echo '<p class="error">Sorry, there was a problem uploading your picture.</p>';
          }
        }      }      else {
        // The new picture file is not valid, so delete the temporary file and set the error flag
        @unlink($_FILES['new_picture']['tmp_name']);
        $error = true;        echo '<p class="error">Your picture must be a GIF, JPEG, or PNG image file no greater than ' . (HR_MAXFILESIZE / 1024) .
          ' KB and ' . HR_MAXIMGWIDTH . 'x' . HR_MAXIMGHEIGHT . ' pixels in size.</p>';      }
    }

    // Update the profile data in the database
    if (!$error) {
      if (!empty($login) && !empty($old_password) && !empty($new_password1) && !empty($new_password2) && ($new_password1 == $new_password2)) {
        // Only set the picture column if there is a new picture
        if (!empty($new_picture)) {
          $query = "UPDATE users SET login = '$login', username = '$username', usersurname = '$usersurname', user_email = '$user_email'," .
            " password = SHA('$new_password1'), user_avatar = '$new_picture' WHERE user_id = '" . $_SESSION['user_id'] . "' AND password = SHA('$old_password')";

        }
        else {
          $query = "UPDATE users SET login = '$login', username = '$username', usersurname = '$usersurname', user_email = '$user_email'," .
            " password = SHA('$new_password1') WHERE user_id = '" . $_SESSION['user_id'] . "' AND password = SHA('$old_password')";
        }
       
        mysqli_query($dbc, $query);

        // Confirm success with the user
        echo '<p>Информация о профиле обновлена: </p>';
		$query = "SELECT login, username, usersurname, user_email, user_avatar FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'";
		$data = mysqli_query($dbc, $query);
		$row = mysqli_fetch_array($data);

		if ($row != NULL) {
		  echo 'Логин: ' . $row['login'] . '<br>';
		  echo 'Имя: ' . $row['username'] . '<br>';
		  echo 'Фамилия: ' . $row['usersurname'] . '<br>';
		  echo 'E-mail: ' . $row['user_email'] . '<br>';
		  echo '<img class="cover circle" src="' . HR_UPLOADPATH . $row['user_avatar'] . '" alt="avatar" />';
		}
		else {
		  echo '<p class="error">There was a problem accessing item profile.</p>';
		}

        echo '<p>Хотите вернуться <a href="index.php">на главную страницу</a>?</p>';
        
        mysqli_close($dbc);
        exit();
      }
      else {
        echo '<p class="error">You must enter all of the profile data (the picture is optional).</p>';
      }
    }
  } // End of check for form submission
  else {
    // Grab the profile data from the database
    $query = "SELECT login, username, usersurname, user_email, user_avatar FROM users WHERE user_id = '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($data);

    if ($row != NULL) {
      $login = $row['login'];
      $username = $row['username'];
      $usersurname = $row['usersurname'];
      $user_email = $row['user_email'];
      $old_picture = $row['user_avatar'];
    }
    else {
      echo '<p class="error">There was a problem accessing your profile.</p>';
    }
  }

  mysqli_close($dbc);
?>

  <form enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HR_MAXFILESIZE; ?>" />
    <fieldset>
      <legend>Личная информация</legend>
      <label for="login">Логин:</label>
      <input type="text" id="login" name="login" value="<?php if (!empty($login)) echo $login; ?>" /><br />
      <label for="username">Имя:</label>
      <input type="text" id="username" name="username" value="<?php if (!empty($username)) echo $username; ?>" /><br />
      <label for="usersurname">Фамилия:</label>
      <input type="text" id="usersurname" name="usersurname" value="<?php if (!empty($usersurname)) echo $usersurname; ?>" /><br />
      <label for="user_email">E-mail:</label>
      <input type="email" id="user_email" name="user_email" value="<?php if (!empty($user_email)) echo $user_email; ?>" /><br />

      
      <label for="old_password">Старый пароль:</label>
      <input type="password" id="old_password" name="old_password" /><br />
      <label for="new_password1">Новый пароль:</label>
      <input type="password" id="new_password1" name="new_password1" /><br />
      <label for="new_password2">Повторите пароль:</label>
      <input type="password" id="new_password2" name="new_password2" /><br />
      
      <input type="hidden" name="old_picture" value="<?php if (!empty($old_picture)) echo $old_picture; ?>" />
      <label for="new_picture">Picture:</label>
      <input type="file" id="new_picture" name="new_picture" />
      <?php if (!empty($old_picture)) {
        echo '<img class="cover circle" src="' . HR_UPLOADPATH . $old_picture . '" alt="Profile Picture" />';
      } ?>
    </fieldset>
    <input type="submit" value="Save Profile" name="submit" />
  </form>
  
<?php
require_once('footer.php');
?>
