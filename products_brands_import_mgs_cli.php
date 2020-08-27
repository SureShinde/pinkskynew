<?php
/**
 * IMP:::: THIS IS FOR MGS BRANDS EXTENSION ONLY
 *
 * https://www.magesolution.com/magento2-shop-by-brand.html
 */

// cli only
(PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) && die('cli only');

//echo 'memory_limit = ' . ini_get('memory_limit') . "\n";
ini_set('memory_limit', '1024M');
//echo 'memory_limit = ' . ini_get('memory_limit') . "\n";


$mage_root_path = realpath(dirname(__FILE__));
//echo $mage_root_path . PHP_EOL;


// read the mysql credentials and open pdo connection
$env_file = $mage_root_path . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "etc" . DIRECTORY_SEPARATOR . "env.php";
//echo $env_file;

$mage_env_array = include $env_file;
//print_r($mage_env_array);

$opt = array(
    PDO::MYSQL_ATTR_FOUND_ROWS => TRUE,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
//	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);
$pdo_conn = new PDO("mysql:host=" . $mage_env_array['db']['connection']['default']['host'] . ";dbname=" . $mage_env_array['db']['connection']['default']['dbname'], $mage_env_array['db']['connection']['default']['username'], $mage_env_array['db']['connection']['default']['password'], $opt);

$products_brands_table_name = $mage_env_array['db']['table_prefix'] . "mgs_brand_product";
$brands_table_name = $mage_env_array['db']['table_prefix'] . "mgs_brand";
$products_table_name = $mage_env_array['db']['table_prefix'] . "catalog_product_entity";


//echo $products_brands_table_name;
//exit;



// get the attribute_id from eav_attribute where attribute_code is "mgs_brand"
$attribute_id = 0;
try {
    $check_attribute_id = "SELECT attribute_id FROM eav_attribute WHERE attribute_code = :attribute_code";

    $pdo_sth1 = $pdo_conn->prepare($check_attribute_id);
    $pdo_sth1->bindValue('attribute_code', "mgs_brand");
    $pdo_sth1->execute();
    $t_arr = $pdo_sth1->fetch(PDO::FETCH_ASSOC);


    if (isset($t_arr['attribute_id']) && $t_arr['attribute_id'] != "") {
        $attribute_id = $t_arr['attribute_id'];
    } else {
        die("No attribute_id present for attribute_code 'mgs_brand' Is MGS Brand extension installed at all?");
    }
} catch (PDOException $e) {
    if (function_exists('pdo_error_handler')) {
        pdo_error_handler($e, $pdo_sth1);
    } else {
        $trace = $e->getTrace();
        echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
    }
}
//echo $attribute_id;
//exit;



// products_brands csv file, open and read
$csv_file = $mage_root_path . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "products_brands.csv";
//echo $csv_file;
//exit;

$csv_file_pointer = fopen($csv_file, "r");

// Headrow
$head = fgetcsv($csv_file_pointer, 4096);

// parse through brands and process

$brand_id_array = array();
$product_id_array = array();

while (($row = fgetcsv($csv_file_pointer, 4096)) !== false) {
    if (isset($row[0]) && $row[0] != "") {
        $sku = trim($row[0]);
        $brand_name = trim($row[1]);

        if ($sku != "" && $brand_name != "") {

            echo $sku . " >> " . $brand_name . " >>>> ";
//            exit;

            // check if brand present in brand_id_array, if not, add
            $option_id = 0;
            if (!in_array($brand_name, $brand_id_array)) {
                $get_brand_id = "SELECT brand_id, option_id FROM " . $brands_table_name . " WHERE name = :name";
                $pdo_sth1 = $pdo_conn->prepare($get_brand_id);
                $pdo_sth1->bindValue('name', $brand_name);
                $pdo_sth1->execute();
                $t_arr = $pdo_sth1->fetch(PDO::FETCH_ASSOC);
                if (isset($t_arr['brand_id']) && $t_arr['brand_id'] != "") {
                    $brand_id_array[$brand_name] = $t_arr['brand_id'];
                    $option_id = $t_arr['option_id'];
                }
            }

            // check if product present in product_id_array, if not, add
            if (!in_array($sku, $product_id_array)) {
                $get_product_id = "SELECT entity_id FROM " . $products_table_name . " WHERE sku = :sku";
                $pdo_sth1 = $pdo_conn->prepare($get_product_id);
                $pdo_sth1->bindValue('sku', $sku);
                $pdo_sth1->execute();
                $t_arr = $pdo_sth1->fetch(PDO::FETCH_ASSOC);
                if (isset($t_arr['entity_id']) && $t_arr['entity_id'] != "") {
                    $product_id_array[$sku] = $t_arr['entity_id'];
                }
            }

            // proceed only if both brand and product exists in system / the arrays
            if (isset($brand_id_array[$brand_name]) && $brand_id_array[$brand_name] != "") {
                if (isset($product_id_array[$sku]) && $product_id_array[$sku] != "") {
                    echo " Add $sku (" . $product_id_array[$sku] . ") with $brand_name (" . $brand_id_array[$brand_name] . ")";

                    // main mgs_brand_product table insert or update
                    $get_product_brand_row = "SELECT entity_id FROM " . $products_brands_table_name . " WHERE product_id = :product_id";
                    $pdo_sth1 = $pdo_conn->prepare($get_product_brand_row);
                    $pdo_sth1->bindValue('product_id', $product_id_array[$sku]);
                    $pdo_sth1->execute();
                    $t_arr = $pdo_sth1->fetch(PDO::FETCH_ASSOC);

                    if(isset($t_arr['entity_id']) && $t_arr['entity_id'] != "") {
                        $insert_update_sql = "UPDATE " . $products_brands_table_name . " SET brand_id = :brand_id WHERE entity_id = :entity_id";
                        $pdo_sth_2 = $pdo_conn->prepare($insert_update_sql);
                        $pdo_sth_2->bindValue('brand_id', $brand_id_array[$brand_name]);
                        $pdo_sth_2->bindValue('entity_id', $t_arr['entity_id']);
                        $pdo_sth_2->execute();

                        echo " Updated";
                    } else {
                        $insert_update_sql = "INSERT INTO " . $products_brands_table_name . " SET brand_id = :brand_id, product_id = :product_id, position=:position";
                        $pdo_sth_2 = $pdo_conn->prepare($insert_update_sql);
                        $pdo_sth_2->bindValue('brand_id', $brand_id_array[$brand_name]);
                        $pdo_sth_2->bindValue('product_id', $product_id_array[$sku]);
                        $pdo_sth_2->bindValue('position', 0);
                        $pdo_sth_2->execute();

                        echo " Added";
                    }


//                    catalog_product_entity_int table insert or update
//                    INSERT INTO `catalog_product_entity_int` (`value_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES (11069,199,0,2587,5704);
                    $insert_update_sql = "INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value) VALUES (:attribute_id, :store_id, :entity_id, :value) ON DUPLICATE KEY UPDATE value=:value";
                    $pdo_sth_2 = $pdo_conn->prepare($insert_update_sql);
                    $pdo_sth_2->bindValue('attribute_id', $attribute_id);
                    $pdo_sth_2->bindValue('store_id', 0);
                    $pdo_sth_2->bindValue('entity_id', $product_id_array[$sku]);
                    $pdo_sth_2->bindValue('value', $option_id);
                    $pdo_sth_2->execute();


//                    catalog_product_index_eav table insert or update
//                    INSERT INTO `catalog_product_index_eav` (`entity_id`, `attribute_id`, `store_id`, `value`, `source_id`) VALUES (2587,199,1,5704,2587);
                    $insert_update_sql = "INSERT INTO catalog_product_index_eav (entity_id, attribute_id, store_id, value, source_id) VALUES (:entity_id, :attribute_id, :store_id, :value, :source_id) ON DUPLICATE KEY UPDATE value=:value";
                    $pdo_sth_2 = $pdo_conn->prepare($insert_update_sql);
                    $pdo_sth_2->bindValue('entity_id', $product_id_array[$sku]);
                    $pdo_sth_2->bindValue('attribute_id', $attribute_id);
                    $pdo_sth_2->bindValue('store_id', 1);
                    $pdo_sth_2->bindValue('value', $option_id);
                    $pdo_sth_2->bindValue('source_id', $product_id_array[$sku]);
                    $pdo_sth_2->execute();




//                    catalogsearch_fulltext_scope1 insert or update
//                    INSERT INTO `catalogsearch_fulltext_scope1` (`entity_id`, `attribute_id`, `data_index`) VALUES (2587,199,'VEEBA');
                    $insert_update_sql = "INSERT INTO catalogsearch_fulltext_scope1 (entity_id, attribute_id, data_index) VALUES (:entity_id, :attribute_id, :data_index) ON DUPLICATE KEY UPDATE data_index=:data_index";
                    $pdo_sth_2 = $pdo_conn->prepare($insert_update_sql);
                    $pdo_sth_2->bindValue('entity_id', $product_id_array[$sku]);
                    $pdo_sth_2->bindValue('attribute_id', $attribute_id);
                    $pdo_sth_2->bindValue('data_index', $brand_name);
                    $pdo_sth_2->execute();







                } else {
                    echo " SKU Not present in system, skip";
                }
            } else {
                echo " Brand Not present in system, skip";
            }
            echo PHP_EOL;

        }
    }
}

fclose($csv_file_pointer);
