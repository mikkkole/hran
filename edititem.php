<?php
  require_once('startsession.php');
  $page_title = 'Редактирование вещи';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');

  // Make sure the user is logged in before going any further.
  //if (!isset($_SESSION['user_id'])) {
    //echo '<p class="login">Please <a href="login.php">log in</a> to access this page.</p>';
    //exit();
  //}
  //else {
    //echo('<p class="login">You are logged in as ' . $_SESSION['login'] . '. <a href="logout.php">Log out</a>.</p>');
  //}

StartDB();

  if (isset($_POST['submit'])) {
    // Grab the data from the POST
    $item_name = mysqli_real_escape_string($dbc, trim($_POST['item_name']));
    $item_id = mysqli_real_escape_string($dbc, trim($_POST['item_id']));
    $item_description = mysqli_real_escape_string($dbc, trim($_POST['item_description']));
    $item_favorite = mysqli_real_escape_string($dbc, trim($_POST['item_favorite']));
    $old_picture = mysqli_real_escape_string($dbc, trim($_POST['old_picture']));
    $error = false;
    $new_picture = load_image();

    // Validate and move the uploaded picture file, if necessary
    if (!empty($new_picture)) {
            // The new picture file move was successful, now make sure any old picture is deleted
            if (!empty($old_picture) && ($old_picture != $new_picture)) {
              @unlink(HR_UPLOADPATH . $old_picture);
            }
          }
          else {
            // The new picture file move failed, so delete the temporary file and set the error flag
            @unlink($_FILES['item_pic']['tmp_name']);
            $error = true;
            echo '<p class="error">Sorry, there was a problem uploading your picture.</p>';
          }
        }
      else {
        // The new picture file is not valid, so delete the temporary file and set the error flag
        @unlink($_FILES['item_pic']['tmp_name']);
        $error = true;
        echo '<p class="error">Your picture must be a GIF, JPEG, or PNG image file no greater than ' . (HR_MAXFILESIZE / 1024) .
          ' KB and ' . HR_MAXIMGWIDTH . 'x' . HR_MAXIMGHEIGHT . ' pixels in size.</p>';
      }
    

    // Update the profile data in the database
    if (!$error) {
         // Only set the picture column if there is a new picture
        if (!empty($new_picture)) {
          $query = "UPDATE items SET item_name = '$item_name', item_description = '$item_description', item_favorite = '$item_favorite', " .
            " item_picture = '$new_picture' WHERE item_id = '$item_id'";
         }
        else {
          $query = "UPDATE items SET item_name = '$item_name', item_description = '$item_description', item_favorite = '$item_favorite' " .
            " WHERE item_id = '$item_id'";
        }
        mysqli_query($dbc, $query);

        // Confirm success with the user
        echo '<p>Информация о вещи обновлена: </p>';
		$query = "SELECT item_name, item_description, item_favorite, item_picture FROM items WHERE item_id = '$item_id'";
		$data = mysqli_query($dbc, $query);
		$row = mysqli_fetch_array($data);

		if ($row != NULL) {
		  echo 'Имя: ' . $row['item_name'] . '<br>';
		  echo 'Описание: ' . $row['item_description'] . '<br>';
		  echo 'Избранное: ' . ($row['item_favorite'] == 1 ? 'Да' : 'Нет') . '<br>';
		  echo '<img class="cover circle" src="' . HR_UPLOADPATH . $row['item_picture'] . '" alt="Picture" />';
		}
		else {
		  echo '<p class="error">There was a problem accessing item profile.</p>';
		}

        echo '<p>Хотите вернуться <a href="index.php">на главную страницу</a>?</p>';
        mysqli_close($dbc);
        exit();
      
    
  } // End of check for form submission
  else {
		if (isset($_GET['item_id'])) {
		$item_id = $_GET['item_id'];
		}
		  else {
			echo '<p class="error">Не выбрана вещь.</p>';
			exit();
		  }
		  
    // Grab the data from the database
    $query = "SELECT item_name, item_description, item_favorite, item_picture FROM items WHERE item_id = '$item_id'";
    $data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_array($data);

    if ($row != NULL) {
      $item_name = $row['item_name'];
      $item_description = $row['item_description'];
      $item_favorite = $row['item_favorite'];
      $old_picture = $row['item_picture'];
    }
    else {
      echo '<p class="error">There was a problem accessing item profile.</p>';
    }
  }

EndDB()
?>

  <form enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HR_MAXFILESIZE; ?>" />
    <input type="hidden" name="item_id" value="<?php echo $_GET['item_id']; ?>" />
    <fieldset>
      <legend>Информация о вещи</legend>
      <label for="item_name">Название:</label>
      <input type="text" id="item_name" name="item_name" value="<?php if (!empty($item_name)) echo $item_name; ?>" /><br />
      <label for="item_description">Описание:</label>
      <input type="text" id="item_description" name="item_description" value="<?php if (!empty($item_description)) echo $item_description; ?>" /><br />
      <label for="item_favorite">Избранное:</label>
      <select id="item_favorite" name="item_favorite">
        <option value="1" <?php if ($item_favorite == '1') echo 'selected = "selected"'; ?>>Да</option>
        <option value="0" <?php if ($item_favorite != '1') echo 'selected = "selected"'; ?>>Нет</option>
      </select><br />
      <input type="hidden" name="old_picture" value="<?php if (!empty($old_picture)) echo $old_picture; ?>" />
      <label for="item_pic">Фото:</label>
      <input type="file" id="item_pic" name="item_pic" />
      <?php if (!empty($old_picture)) {
        echo '<a href="' . HR_UPLOADPATH . $old_picture . '" target="_blank">
              <img class="cover circle" src="' . HR_UPLOADPATH . $old_picture . '" alt="Picture" /></a>';
              } ?>
    </fieldset>
    <input type="submit" value="Сохранить изменений" name="submit" />
  </form>
  
<?php
require_once('footer.php');
