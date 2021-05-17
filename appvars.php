<?php
  // Define application constants
  define('HR_UPLOADPATH', 'pics/');
  define('HR_MAXFILESIZE', 32768000);      // 32 MB
  define('HR_MAXIMGDIMENTION', 500);        // 500 pixels
  define('HR_MAXIMGWIDTH', 500);        // 500 pixels
  define('HR_MAXIMGHEIGHT', 500);        // 500 pixels


function StartDB()  //функция соединения с базой данных
{
	global $dbc;
	$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	if (mysqli_connect_errno()) 
	{
		print "Не удалось подключиться: %s\n".mysqli_connect_error();
		exit();
	}
	//else {
		//print "Удалось подключиться!!!<br>";
	//}
	mysqli_set_charset($dbc, "utf8");
}

function EndDB()  //функция закрытия соединения с базой данных
{
	global $dbc;
	mysqli_close($dbc);
}	

  
function ShowItems() {  //функция вывода элементов. Доработать зависимость от выбранной полки

if (isset($_GET['shelf_id'])) {  // получаем номер полки по ссылке. Если нет, то по умолчанию (первый шкаф).
	$shelf_id = $_GET['shelf_id'];
}
else {
	$shelf_id = 1;
}	 

	global $dbc;
	$query = "SELECT * FROM items WHERE shelf_id = '$shelf_id'";
	if ($result = mysqli_query($dbc, $query)) {
		printf ("Число строк в запросе: %d<br>", mysqli_num_rows($result));
		// Выборка результатов запроса 
		while($row = mysqli_fetch_assoc($result)) { 
			echo '<div style="float: left; padding: 5;">
				  <a href="edititem.php?item_id=' . $row['item_id'] . '" alt="' . $row['item_name'] . '" title="Редактировать ' . $row['item_name'] . '">
				  <img src="' . HR_UPLOADPATH . $row['item_picture'] . '" class="cover circle"></a></div>';	
		} 
		mysqli_free_result($result);
	}
	else {
		printf("Ошибка в запросе: %s\n", mysqli_error($dbc));
	}
}	

function ShowShelfs() {  //функция вывода шкафов и полок
	 
if (isset($_GET['shelf_id'])) {  // получаем номер полки по ссылке. Если нет, то по умолчанию (первый шкаф).
	$shelf_id = $_GET['shelf_id'];
}
else {
	$shelf_id = 1;
}	 

	global $dbc;
	$chosen_shelf = $shelf_id;
	$query = "SELECT * FROM shelfs WHERE shelf_id = '$shelf_id'"; // ищем выбраннцю полку
	$data = mysqli_query($dbc, $query);
    $row = mysqli_fetch_assoc($data);
	if ($row != NULL) {
		$shelf_parent_id = $row['shelf_parent_id'];				
		$shelf_structure = array();
		// если нашли, то вносим все вложенные в выбранную полку в массив, чтобы отобразить структуру вниз
		$query = "SELECT * FROM shelfs WHERE shelf_parent_id = '$shelf_id' ORDER BY shelf_id"; 
			$data = mysqli_query($dbc, $query);
			while ($row = mysqli_fetch_assoc($data)) {
				$row['chosen'] = 'no'; // признак, что полка не выбрана в цепочке. Для выделения цветом.
				$shelf_structure[$shelf_id][] = $row;				
			}
		while ($shelf_parent_id != 0){ // находим все полки с такой же родительской полкой
			$query = "SELECT * FROM shelfs WHERE shelf_parent_id = '$shelf_parent_id' ORDER BY shelf_id";
			$data = mysqli_query($dbc, $query);

			while ($row = mysqli_fetch_assoc($data)) {
			$row['shelf_id'] == $chosen_shelf ? $row['chosen'] = 'yes' : $row['chosen'] = 'no'; // признак, что полка выбрана в цепочке. Для выделения цветом.
			$shelf_structure[$shelf_parent_id][] = $row; // вносим все найденные полки в массив
			}
			//ищем родителькую полку у родительской полки - и так до нулевого уровня
			$query = "SELECT * FROM shelfs WHERE shelf_id = '$shelf_parent_id' ORDER BY shelf_id";	
			$data = mysqli_query($dbc, $query);
			$row = mysqli_fetch_assoc($data);
			$chosen_shelf = $shelf_parent_id;
			$shelf_parent_id = $row['shelf_parent_id'];
		}
		// после нахождения всей цепочки родительских полок добавляем полки (шкафы) нулевого уровня
		$query = "SELECT * FROM shelfs WHERE shelf_parent_id = 0 ORDER BY shelf_id"; 
		$data = mysqli_query($dbc, $query);
		while ($row = mysqli_fetch_assoc($data)) {
			$row['shelf_id'] == $chosen_shelf ? $row['chosen'] = 'yes' : $row['chosen'] = 'no'; // признак, что полка выбрана в цепочке. Для выделения цветом.
			$shelf_structure[0][] = $row;
		}
//r ($shelf_structure);
		$shelf_structure = array_reverse($shelf_structure);
			foreach ($shelf_structure as $shelf => $massive) { // выводим полки строками в зависимости от родительской полки
				echo '<div class="polka">';
				foreach ($massive as $shelf2 => $massive2) {
					echo '<a href="' . $_SERVER['PHP_SELF'] . '?shelf_id=' . $massive2['shelf_id'] . '" alt="' . $massive2['shelf_name'] . '"' . 
					($massive2['chosen'] == 'yes' ? 'class="polka_chosen"' : '') . '>'. $massive2['shelf_name'] . ' </a>';
				}
				echo '<a href="addshelf.php"> добавить элемент на уровень.</a>';
				echo '</div>';
			}
	} // конец проверки найдена ли полка
		
    else {
      printf("Ошибка в запросе: %s\n", mysqli_error($dbc));
    }
}	

function r($some_array) {  //функция для быстрого просмотра массивов при отладке
	echo '<pre>';
	print_r($some_array);
	echo '</pre>';
}	

function load_image () {	//функция уменьшения и обрезания (если будет нужно) картинок при загрузке
	$realPath = realpath($_FILES['item_pic']['tmp_name']);	//imagik передает всю работу в ImageMagick, поэтому нужно указывать полный путь.
	
	if ($realPath === false) {
	throw new \Exception("File was not located at " . $_FILES['item_pic']['name']);
	}
	
	$extension = pathinfo($_FILES['item_pic']['name'], PATHINFO_EXTENSION);
	$item_pic = time() . $_SESSION['user_id'] . '.' . $extension;  //имя картинки будет состоять из времени загрузки и id пользователя
	$item_pic_size = $_FILES['item_pic']['size'];
	$item_pic_type = $_FILES['item_pic']['type'];

    if (!empty($_FILES['item_pic']['name'])) { // проверка, есть ли картинка
		if ((($item_pic_type == 'image/gif') || 
			 ($item_pic_type == 'image/jpeg') || 
			 ($item_pic_type == 'image/pjpeg') || 
			 ($item_pic_type == 'image/png')) && 
			 ($item_pic_size > 0) && 
			 ($item_pic_size <= HR_MAXFILESIZE)) {
				 
				 if ($_FILES['item_pic']['error'] == 0) {
					$target = $_SERVER['DOCUMENT_ROOT'] . 'hran/' . HR_UPLOADPATH . $item_pic; // опять нужен полный путь для imagik
					$im = new imagick($realPath);
					$imageprops = $im->getImageGeometry();
					$width = $imageprops['width'];
					$height = $imageprops['height'];
					
					if($width > $height){ //делаем высоту (или ширину) равной константе, а ширину (или высоту) уменьшаем пропорционально
					    $new_height = HR_MAXIMGDIMENTION;
					    $new_width = (HR_MAXIMGDIMENTION / $height) * $width;
					}
					else{
					    $new_width = HR_MAXIMGDIMENTION;
					    $new_height = (HR_MAXIMGDIMENTION / $width) * $height;
					}
					$im->resizeImage($new_width,$new_height, imagick::FILTER_LANCZOS, 0.9, true);
					//$im->cropImage (300,300,0,0); // на случай необходимомти обрезать картинку
					
						if ($im->writeImage($target)) {
							  return $item_pic;
							}
							else {
							  echo '<p class="error">Ошибка загрузки файла.</p>';
							}
				 }
			}
			else {
				echo '<p class="error">Файл должен быть изображением и не превышать ' . (HR_MAXFILESIZE / 1024) . ' Кб.</p>';
			}
	@unlink($_FILES['item_pic']['tmp_name']); //Удаляем временный файл изображения. Подавляем сообшение об ошибке с помощью @
	
	} // конец проверки, есть ли картинка
	else {
			echo '<p class="error">Ошибка загрузки фото.</p>';
	}
}


function generate_sort_links($user_search, $sort){  //функция ссылок для сортировки
	$sort_links = '';
	switch ($sort) {
		case 1:
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=2">Название Вещи</a></td><td>Описание</td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=3">Полка</a></td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=5">Избранное</a></td><td>Фото</td>';
		break;
		
		case 3:
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=1">Название Вещи</a></td><td>Описание</td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=4">Полка</a></td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=5">Избранное</a></td><td>Фото</td>';
		break;
		
		case 5:
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=1">Название Вещи</a></td><td>Описание</td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=3">Полка</a></td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=6">Избранное</a></td><td>Фото</td>';
		break;

		default:
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=1">Название Вещи</a></td><td>Описание</td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=3">Полка</a></td>';
			$sort_links .= '<td><a href = "' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=5">Избранное</a></td><td>Фото</td>';
		}
		return $sort_links;
	}

function build_query($user_search, $sort){ // функция запроса БД по поиску
	  $clean_search = str_replace(',', ' ', $user_search);
	  $search_words = explode(' ', $clean_search);
	  $final_search_words = array();
	  if (count($search_words) > 0) {
		  foreach($search_words as $word){
			  if (!empty ($word)){
				  $final_search_words[] = $word;
			  }
		  }
	  }
	  
	  $search_query = "SELECT * FROM items";
	  $where_list = array();
	  
	  if (count($final_search_words) > 0) {
			foreach ($final_search_words as $word) {
				$where_list[] = "item_description LIKE '%$word%'";
			}
		}

		  $where_clause = implode(' OR ', $where_list);
		  
		  if (!empty ($where_clause)) {
			  $search_query .= " WHERE $where_clause";
		  }
		  
		  switch ($sort){
			  case 1:
				$search_query .= " ORDER BY item_name";
			  break;
			  
			  case 2:
				$search_query .= " ORDER BY item_name DESC";
			  break;

			  case 3:
				$search_query .= " ORDER BY shelf_id";
			  break;

			  case 4:
				$search_query .= " ORDER BY shelf_id DESC";
			  break;

			  case 5:
				$search_query .= " ORDER BY item_favorite";
			  break;

			  case 6:
				$search_query .= " ORDER BY item_favorite DESC";
			  break;

			  default:			  
		  }
		 return $search_query;
}

function generate_page_links ($user_search, $sort, $cur_page, $num_pages) {	 // функция разбивки результатов на страницы
	$page_links = '';
	if ($cur_page > 1){
		$page_links .= '<a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=' . $sort . '&page=' . ($cur_page -1) . '"><-</a> ';
	}
	else {
		$page_links .= '<- ';
	}
	for ($i = 1; $i <= $num_pages; $i++) {
		if ($cur_page == $i) {
			$page_links .= ' ' . $i;
		}
		else {
			$page_links .= '<a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=' . $sort . '&page=' . $i . '">' . $i . '</a> ';
		}
	}
	if ($cur_page < $num_pages){
		$page_links .= '<a href="' . $_SERVER['PHP_SELF'] . '?usersearch=' . $user_search . '&sort=' . $sort . '&page=' . ($cur_page +1) . '">-></a> ';
	}
	else {
		$page_links .= ' ->';
	}
	return $page_links;
}
