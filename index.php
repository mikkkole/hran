<?php
  require_once('startsession.php');
  $page_title = 'Где все вещи на местах.';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');
  
StartDB();

echo '<div id="wrapper">
		<div id="sidebar">
			<li><a href="#">Весь список</a></li>
			<li><a href="#">Весь список</a></li>
			<li><a href="#">Весь список</a></li>
		</div> <!-- end #sidebar -->
		
		<div id="content">';

ShowShelfs();
ShowItems();

if (isset($_POST['submit'])) {
	$name = $_POST['name'];
	$item_pic = load_image();
	
	// Write the data to the database
	$query = "INSERT INTO items (item_name, item_picture) VALUES ('$name', '$item_pic')";
	$result = mysqli_query($dbc, $query);
	echo '<p><strong>Result:</strong> ' . $result . '<br />';
	
	// Confirm success with the user
	echo '<p>Thanks for adding your item!</p>';
	echo '<p><strong>Name:</strong> ' . $name . '<br />';
	echo '<div style="float: left; padding: 5;">
	<a href="' . HR_UPLOADPATH . $item_pic . '" target="_blank">
	<img src="' . HR_UPLOADPATH . $item_pic . '" width="320" height="250" class="cover circle"></a></div>';	
	//Здесь нельзя поставить ссылку на редактирование только что добавленной вещи, т.к. item_id только что создан в БД и еще не запрашивался. Подумать, нужно ли делать запрос и ставить такую ссылку.
	// Clear the score data to clear the form
	$name = "";
	$item_pic = "";
  }
?>

<div style="clear: both;">
<br>	
  <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HR_MAXFILESIZE; ?>" />
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php if (!empty($name)) echo $name; ?>" /><br />
	<br />
	<label for="item_pic">Добавьте вещь: </label>
	<input type="file" id="item_pic" name="item_pic" />
<!--<input type="file" name="item_pic[]" multiple accept="image/*,image/jpeg"> -->
    <input type="submit" value="Add" name="submit" />
  </form>
</div>	
<hr />

</div>
<?php 

EndDB();
  
require_once('footer.php');

