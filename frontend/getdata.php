<?php
ini_set('display_errors', 1);

if ($_GET['scraper'] == 'engel') {
    require "db.php";
    $sql = "SELECT * from product";
    if(isset($_GET["cat"])) {
        $sql = "SELECT * from product WHERE cat = $_GET[cat]";
    }
    $result = $conn->query($sql);
    $json = [];

    while($row = $result->fetch_assoc()) {
        $sql2 = "SELECT * from size WHERE product_id = '$row[article_num]'";
        $result2 = $conn->query($sql2);
        $row['sizes'] = $result2->fetch_all(MYSQLI_ASSOC);
        $sql2 = "SELECT * from images WHERE article_num = '$row[article_num]'";
        $result2 = $conn->query($sql2);
        $row['images'] = $result2->fetch_all(MYSQLI_ASSOC);
        $json[] = $row;
    }

}
else if ($_GET['scraper'] == 'kwon') {
    require "db2.php";
    $sql = "SELECT * from product";
    $result = $conn->query($sql);
    $json = [];
    while($row = $result->fetch_assoc()) {
        $sql2 = "SELECT * from product_history WHERE article_number = '$row[article_number]'";
        $result2 = $conn->query($sql2);
        $row['history'] = $result2->fetch_all(MYSQLI_ASSOC);
        $json[] = $row;
    }
}

echo json_encode(["data" => $json]);