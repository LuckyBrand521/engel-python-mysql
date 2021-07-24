<?php
	set_time_limit(10000);
	ini_set('max_execution_time', '10000');
	session_start(['cookie_path'=>basename(__DIR__)]);
	if(!isset($_SESSION['login'])) {
		header("Location: index.php");
		die();
	}
	require "db.php";
	$category = "All";
	if(isset($_GET['cat'])) {
		$result = $conn->query("SELECT * FROM category WHERE cat_id = $_GET[cat]")->fetch_assoc();
		$category = $result['name'];
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Naturstein Crawler</title>
		<link rel="stylesheet" href="style.css">
		<script src="https://kit.fontawesome.com/0b3f70179b.js" crossorigin="anonymous"></script>
		<script
		src="https://code.jquery.com/jquery-3.5.1.min.js"
		integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
		crossorigin="anonymous"></script>
		

		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
		<script src="https://cdn.datatables.net/plug-ins/1.10.22/sorting/datetime-moment.js"></script>
	</head>
	<body>
	

	<div class="sidenav">
		<ul>
			<li><a href="?">All</a></li>
			<?php
				$sql = "SELECT * from category WHERE parent_id IS NULL";
				$result = $conn->query($sql);
				while($row = $result->fetch_assoc()) {
					echo "<li><a href='?cat=$row[cat_id]'>$row[name]</a>";
					$sql2 = "SELECT * from category WHERE parent_id = $row[cat_id]";
					$result2 = $conn->query($sql2);
					if($result2->num_rows > 0) {
						echo "<ul>";
						while($row2 = $result2->fetch_assoc()) {
							echo "<li><a href='?cat=$row2[cat_id]'>$row2[name]</a>";
						
							$sql3 = "SELECT * from category WHERE parent_id = $row2[cat_id]";
							$result3 = $conn->query($sql3);
							if($result3->num_rows > 0) {
								echo "<ul>";
								while($row3 = $result3->fetch_assoc()) {
									echo "<li><a href='?cat=$row3[cat_id]'>$row3[name]</a>";
									$sql4 = "SELECT * from category WHERE parent_id = $row3[cat_id]";
									$result4 = $conn->query($sql4);
									if($result4->num_rows > 0) {
										echo "<ul>";
										while($row4 = $result4->fetch_assoc()) {
											echo "<li><a href='?cat=$row4[cat_id]'>$row4[name]</a>";
										}
										echo "</li>";
										echo "</ul>";
									}
								}				
								echo "</li>";
								echo "</ul>";
							}
						}
						echo "</li>";
						echo "</ul>";
					}
					
					echo "</li>";
				}
			?>
			</li>
		</ul>
	</div>

	<div class="main">

	<a href="export.php">Export CSV</a>
	<br>
	<a href="exportshopware.php">Export Shopware CSV</a>

	<h1><?php echo $category; ?></h1>

	<table id="table_id" class="display">
		<thead>
			<tr>
				<th></th>
				<th style="text-align: center" scope="col">Artikelnummer</th>
				<th style="text-align: center" scope="col">Überschrift</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

	</div>

	<script src="main.js"></script>
</body>
</html>