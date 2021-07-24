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
	
	<?php require 'sidebar.php'; ?>

	<div style="margin-left: 215px; margin-top: 15px;">
	<table id="table_id" class="display">
		<thead>
			<tr>
				<th></th>
				<th style="text-align: center" scope="col">Artikelnummer</th>
				<th style="text-align: center" scope="col">Überschrift</th>
				<th style="text-align: center" scope="col">Preis</th>
				<th style="text-align: center" scope="col">EK Preis</th>
				<th style="text-align: center" scope="col">UVP</th>
				<th style="text-align: center" scope="col">Selection</th>
				<th style="text-align: center" scope="col">Category</th>
				<th style="text-align: center" scope="col">Last Date</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

	</div>

	<script src="kwon.js"></script>
</body>
</html>