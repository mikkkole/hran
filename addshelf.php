<?php
  require_once('startsession.php');
  $page_title = 'Добавление шкафа/полки.';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');
  
StartDB();

if (isset($_POST['submit'])) {
	$shelf_name = mysqli_real_escape_string($dbc, trim($_POST['shelf_name']));
	$shelf_parent_id = mysqli_real_escape_string($dbc, trim($_POST['shelf_parent_id']));
	$shelf_description = mysqli_real_escape_string($dbc, trim($_POST['shelf_description']));
	$shelf_favorite = mysqli_real_escape_string($dbc, trim($_POST['shelf_favorite']));
	$shelf_pic = $_FILES['shelf_pic']['name'];
	$shelf_pic_size = $_FILES['shelf_pic']['size'];
	$shelf_pic_type = $_FILES['shelf_pic']['type'];
	
    if (!empty($shelf_pic)) {
		$shelf_pic = time() . $_FILES['shelf_pic']['name'];
		if ((($shelf_pic_type == 'image/gif') || 
			 ($shelf_pic_type == 'image/jpeg') || 
			 ($shelf_pic_type == 'image/pjpeg') || 
			 ($shelf_pic_type == 'image/png')) && 
			 ($shelf_pic_size > 0) && 
			 ($shelf_pic_size <= HR_MAXFILESIZE)) {
				 
				 if ($_FILES['shelf_pic']['error'] == 0) {
					$target = HR_UPLOADPATH . $shelf_pic;
						if (move_uploaded_file($_FILES['shelf_pic']['tmp_name'], $target)) {
							  // Write the data to the database
							  $query = "INSERT INTO shelfs (user_id, shelf_parent_id, shelf_name, shelf_description, shelf_picture, shelf_favorite) " .
							  " VALUES ('" . $_SESSION['user_id'] . "', '$shelf_parent_id', '$shelf_name', '$shelf_description', '$shelf_pic', '$shelf_favorite')";
							  $result = mysqli_query($dbc, $query);
							  echo '<p><strong>Результат:</strong> ' . $result . '<br />';

							  // Confirm success with the user
							  echo '<p>Вы добавили шкаф/полку!</p>';
							  echo '<p><strong>Название: </strong> ' . $shelf_name . '<br />';
							  echo '<p><strong>Описание: </strong> ' . $shelf_description . '<br />';
							  echo '<p><strong>Находится на уровне: </strong> ' . $shelf_parent_id . '<br />';
							  echo '<p><strong>Избранное: </strong>' . ($shelf_favorite == 1 ? 'Да' : 'Нет') . '<br>';
							  echo '<div style="float: left; padding: 5;">
							  <a href="' . HR_UPLOADPATH . $shelf_pic . '" target="_blank">
							  <img src="' . HR_UPLOADPATH . $shelf_pic . '" width="320" height="250" class="cover circle"></a></div>';	
							  //Здесь нельзя поставить ссылку на редактирование только что добавленной вещи, т.к. item_id только что создан в БД и еще не запрашивался. Подумать, нужно ли делать запрос и ставить такую ссылку.
							  // Clear the score data to clear the form
							  $name = "";
							  $shelf_pic = "";
							  echo '<p><strong>Добавить еще одну?<strong></p>';
			  
							}
							else {
							  echo '<p class="error">Ошибка загрузки файла.</p>';
							}
				 }
			}
			else {
				echo '<p class="error">Файл должен быть изображением и не превышать ' . (HR_MAXFILESIZE / 1024) . ' Кб.</p>';
			}
		//Попытка удалить временный файл изображения. Подавляем сообшение об ошибке с помощью @
	@unlink($_FILES['shelf_pic']['tmp_name']);
	}
	else { //добавляем без картинки
			$query = "INSERT INTO shelfs (user_id, shelf_parent_id, shelf_name, shelf_description, shelf_favorite) " .
			" VALUES ('" . $_SESSION['user_id'] . "', '$shelf_parent_id', '$shelf_name', '$shelf_description', '$shelf_favorite')";
			$result = mysqli_query($dbc, $query);
			echo $query;
			echo $result;
			echo '<p><strong>Результат:</strong> ' . $result . '<br />';

			// Confirm success with the user
			echo '<p>Вы добавили шкаф/полку!</p>';
			echo '<p><strong>Название: </strong> ' . $shelf_name . '<br />';
			echo '<p><strong>Описание: </strong> ' . $shelf_description . '<br />';
			echo '<p><strong>Находится на уровне: </strong> ' . $shelf_parent_id . '<br />';
			echo '<p><strong>Избранное: </strong>' . ($shelf_favorite == 1 ? 'Да' : 'Нет') . '<br>';
			echo '<p><strong>Добавить еще одну?<strong></p>';
			$name = "";
			$shelf_pic = "";

			}
  }
?>

<div style="clear: both;">
<br>	
  <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HR_MAXFILESIZE; ?>" />
    <label for="shelf_name">Название:</label>
    <input type="text" id="shelf_name" name="shelf_name" value="<?php if (!empty($shelf_name)) echo $shelf_name; ?>" /><br />
    
    <label for="shelf_description">Описание:</label>
    <input type="text" id="shelf_description" name="shelf_description" value="<?php if (!empty($shelf_description)) echo $shelf_description; ?>" /><br />

    
    <label for="shelf_parent_id">Уровень:</label>
    <input type="text" id="shelf_parent_id" name="shelf_parent_id" value="<?php if (!empty($shelf_parent_id)) echo $shelf_parent_id; ?>" /><br />
    
    
    <label for="shelf_favorite">Избранное:</label>
      <select id="shelf_favorite" name="shelf_favorite">
        <option value="1" >Да</option>
        <option value="0" >Нет</option>
    </select><br />
    
	<label for="shelf_pic">Добавьте фото: </label>
	<input type="file" id="shelf_pic" name="shelf_pic" />
    <input type="submit" value="Add" name="submit" />
  </form>
</div>	
<hr />

<?php 

EndDB();
  
require_once('footer.php');
?>
