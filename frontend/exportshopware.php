<?php

require "db.php";
$delivery = [
    "delivery--status-available" => "3",
    "delivery--status-more-is-coming" => "10",
    "delivery--status-not-available" => "20",
    "unknown" => "22"
];
$sql = "SELECT * FROM product INNER JOIN size ON (product.article_num = size.product_id)";
$result = $conn->query($sql);

function removeNumbers($key) {
    return ($key >= ord("A") && $key <= ord("Z") || $key >= ord("a") && $key <= ord("z"));
}

function charCount($str) {
   return count(array_filter(count_chars($str, 1), "removeNumbers", ARRAY_FILTER_USE_KEY));
}

function numberCount($str) {
    return count(array_filter(str_split($str),'is_numeric'));
}

$fp = fopen(__DIR__ . '/exportshopware.csv', 'w');
fputcsv($fp, ["ordernumber", "mainnumber", "name", "additionalText", "coulourName", "coulourNumber", "size", "supplier", "tax", "price_EK", "pseudoprice_EK", "baseprice_EK", "price_H", "pseudoprice_H", "baseprice_H", "active", "instock", "stockmin", "description", "description_long", "shippingtime", "added", "changed", "releasedate", "shippingfree", "topseller", "keywords", "minpurchase", "purchasesteps", "maxpurchase", "purchaseunit", "referenceunit", "packunit", "unitID", "pricegroupID", "pricegroupActive", "laststock", "suppliernumber", "weight", "width", "height", "length", "ean", "similar", "configuratorsetID", "configuratortype", "configuratorOptions", "categories", "propertyGroupName", "propertyValueName", "accessory", "imageUrl", "main", "attr1", "attr2", "attr3", "purchasePrice", "metatitle"]);
while($row = $result->fetch_assoc()) {
    $original_article = $row['article_num'];
    if(strpos($row['article_num'], "-")) {
        $row['article_num'] = explode("-", $row['article_num'])[0];
    }
    if($conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock IN ('delivery--status-available', 'delivery--status-more-is-coming', 'delivery--status-not-available') ")->fetch_assoc()['count'] == 1 && $conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock = 'unknown'")->fetch_assoc()['count'] == 1) {
        if($row['stock'] == 'unknown') continue;
    }
    else if ($conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock = 'unknown'")->fetch_assoc()['count'] == 2 || $conn->query("SELECT COUNT(*) as `count` FROM size WHERE product_id LIKE '$row[article_num]%' AND variant = '$row[variant]' AND size = '$row[size]' AND stock IN ('delivery--status-available', 'delivery--status-more-is-coming', 'delivery--status-not-available')")->fetch_assoc()['count'] == 2) {
        if($original_article == $row['article_num']) continue;
    }
    $sku = $row['article_num'] . "-" . explode("-", $row['variant'])[0] . "-" . str_replace("/", "", $row['size']);
    $delivery_time = $delivery[$row['stock']];
    if($conn->query("SELECT COUNT(*) as `count` FROM images WHERE article_num = '$original_article'")->fetch_assoc()['count'] == 1) {
        $img_url = $conn->query("SELECT * FROM images WHERE article_num = '$original_article'")->fetch_assoc()['img_url'];
    }
    else {
        $img_var = explode("-", $row['variant'])[0];
        preg_match("/([0-9]+)([A-Z]+)/", $img_var, $matches);
        if(count($matches) >= 3) {
            $img_var2 = "$matches[1]_$matches[2]";
            $img_url = $conn->query("SELECT * FROM images WHERE article_num = '$original_article' AND (img_url LIKE '%$img_var%' OR img_url LIKE '%$img_var2%' OR alt LIKE '%$img_var%')")->fetch_assoc()['img_url'];
        }
        else {
            $img_url = $conn->query("SELECT * FROM images WHERE article_num = '$original_article' AND (img_url LIKE '%$img_var%' OR alt LIKE '%$img_var%')")->fetch_assoc()['img_url'];
        }
    }
    $colorName = explode("-", $row['variant'], 2)[1];
    $colorNumber = explode("-", $row['variant'], 2)[0];
    $parts = explode("-", $sku);
    $last = $parts[count($parts)-1];
    $secondlast = $parts[count($parts)-2];
    $size = '';
    if(in_array($last, ["XS", "S", "M", "L", "XL", "XXL", "XXXL"])) {
        $size = $last;
    }
    else if(numberCount($last) <= 3 && numberCount($last) >= 1) {
        $size = $last;
    }
    else if(numberCount($last) == 4) {
        $size = substr($last, 0, 2) . "-" . substr($last, 2, 2);
    }
    else if(numberCount($last) == 5) {
        $size = substr($last, 0, 2) . "-" . substr($last, 2, 3);
    }
    else if(numberCount($last) == 6) {
        $size = substr($last, 0, 3) . "-" . substr($last, 3, 3);
    }
    else if(numberCount($secondlast) == 4 && charCount($last) > 0) {
        $size = substr($secondlast, 0, 2) . "-" . substr($secondlast, 2, 2);
    }
    else if(numberCount($secondlast) == 5 && charCount($last) > 0) {
        $size = substr($secondlast, 0, 2) . "-" . substr($secondlast, 2, 3);
    }
    else if(numberCount($secondlast) == 2 && charCount($last) > 0 && strstr($last, "S") == false && strstr($last, "M") == false && strstr($last, "L") == false) {
        $size = $last;
    }
    if($row['article_num'] == "101160" || $row['article_num'] == "101170") {
        $colorName = '';
        $colorNumber = '';
        //$sku = '';
    }
    $conn->query("UPDATE size SET sku='$sku' WHERE product_id='$original_article' AND variant='$row[variant]' AND size='$row[size]'");
    fputcsv($fp, [$sku, $row['article_num'], $row['title'], $row['variant'], $colorName, $colorNumber, $size, "Engel GmbH", "19", $row['price'], str_replace(".", ",", $row['UVP']), "", "", "", "", "", "", "", "", "", $delivery_time, "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", str_replace(".", ",", $row['weight']), "", "", "", $row['EAN'], "", "", "", "", "", "", "", "", $img_url, "", "", "", "", ""]);
}
fclose($fp);
header("Location: exportshopware.csv");