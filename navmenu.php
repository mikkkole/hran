<?php

echo '<div id="wrapper">
       <div id="nav">';
if (isset($_SESSION['login'])) {
echo   '<a href="index.php">Твое Хранилище</a>
		<a href="#">инструменты</a>
		<a href="editprofile.php">Настройки</a>
		<a href="logout.php">Выход (' . $_SESSION['login'] . ')</a>
	  </div> 
	
	  <div id="nav">
		<a href="#">Все шкафы</a>
		<a href="#">поиск по изображению</a>
		
		<form method="get" action="search.php">
			<label for="usersearch">Поиск вещи:</label>
			<input type="text" id="usersearch" name="usersearch" />
			<input type="submit" name="submit" value="Submit" />
		</form>'; 
		
}
else {
echo   '<a href="index.php">Твое Хранилище</a>
		<a href="login.php">инструменты</a>
		<a href="login.php">настройки</a>
		<a href="login.php">войти</a>
		<a href="signup.php">регистрация</a>
	  </div> 
	
	  <div id="nav">
		<a href="login.php">Все шкафы</a>
		<a href="login.php">поиск вещи</a>
		<a href="login.php">поиск по изображению</a>'; 
}
echo '</div> 
	 </div>';
?>
