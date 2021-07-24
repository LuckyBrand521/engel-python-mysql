<?php
ini_set('display_errors', 1);
require "db.php";
$delivery = [
    "delivery--status-available" => "3",
    "delivery--status-more-is-coming" => "10",
    "delivery--status-not-available" => "20",
    "unknown" => "22"
];
$sql = "SELECT * FROM product INNER JOIN size ON (product.article_num = size.product_id)";
$result = $conn->query($sql);

$fp = fopen(__DIR__ . '/export.csv', 'w');
fputcsv($fp, ["SKU", "Lagerbestand", "Lieferung"]);
$already_duplicated = [];
while($row = $result->fetch_assoc()) {
    if(strpos($row['article_num'], "-")) {
        $row['article_num'] = explode("-", $row['article_num'])[0];
    }
    if($conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock IN ('delivery--status-available', 'delivery--status-more-is-coming', 'delivery--status-not-available')")->fetch_assoc()['count'] == 1 && $conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock = 'unknown'")->fetch_assoc()['count'] == 1) {
        if($row['stock'] == 'unknown') continue;
    }
    else if ($conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock = 'unknown'")->fetch_assoc()['count'] == 2 || $conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock IN ('delivery--status-available', 'delivery--status-more-is-coming', 'delivery--status-not-available')")->fetch_assoc()['count'] == 2) {
        if(!array_key_exists($row['article_num'], $already_duplicated)) {
            $already_duplicated[$row['article_num']] = true;
        }
        else {
            continue;
        }
    }
    $sku = $row['article_num'] . "-" . explode("-", $row['variant'])[0] . "-" . str_replace("/", "", $row['size']);
    $stock = $row['stock'] == 'unknown' ? "0" : "8";
    $deli = $delivery[$row['stock']];
    fputcsv($fp, [$sku, $stock, $deli]);
}
fclose($fp);
header("Location: export.csv");