<?php
  require_once('startsession.php');
  $page_title = 'Поиск вещей.';
  require_once('header.php');
  require_once('appvars.php');
  require_once('connectvars.php');
  require_once('navmenu.php');
  
// Получаем параметры сортровки и ключевые слова через GET
$sort = isset($_GET['sort']) ? $_GET['sort'] : 1;
$user_search = $_GET['usersearch'];
$cur_page = isset($_GET['page']) ? $_GET['page'] : 1;
$result_per_page = 5;
$skip = (($cur_page - 1) * $result_per_page);
  
echo '<table border="0" cellpadding="2">'; // Вывод результатов
echo '<tr>';
echo generate_sort_links($user_search, $sort);
echo '</tr>';

StartDB();
$search_query = build_query($user_search, $sort);
  
echo $search_query;
  
$result = mysqli_query($dbc, $search_query);
$total = mysqli_num_rows($result);
$num_pages = ceil($total / $result_per_page);
  
$query = $search_query . " LIMIT $skip, $result_per_page";
$result = mysqli_query($dbc, $query);

while ($row = mysqli_fetch_array($result)) {
	echo '<tr>';
	echo '<td valign="top" width="20%"><a href="edititem.php?item_id=' . $row['item_id'] . '" alt="' . $row['item_name'] . '" title="Редактировать ' . 
		$row['item_name'] . '">' . $row['item_name'] . '</a></td>';
	echo '<td valign="top" width="50%">' . substr($row['item_description'], 0, 100) . '...</td>';
	echo '<td valign="top" width="10%"><a href="index.php?shelf_id=' . $row['shelf_id'] . '" alt="' . $row['shelf_id'] . '">'. $row['shelf_id'] . '</a></td>';
	echo '<td valign="top" width="10%">' . ($row['item_favorite'] == 1 ? '&#10084' : '&#10007') . '</td>';
	echo '<td valign="top" width="10%"><a href="edititem.php?item_id=' . $row['item_id'] . '" alt="' . $row['item_name'] . '" title="Редактировать ' . 
		$row['item_name'] . '"><img src="' . HR_UPLOADPATH . $row['item_picture'] . '" class="cover small_circle"></a></td>';
	echo '</tr>';
} 
echo '</table>';
  
if ($num_pages >1){
  echo generate_page_links($user_search, $sort, $cur_page, $num_pages);
}

EndDB();
  
require_once('footer.php');

