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

$brand_table_name = $mage_env_array['db']['table_prefix'] . "mgs_brand";
$brand_store_table_name = $mage_env_array['db']['table_prefix'] . "mgs_brand_store";

//echo $brand_table_name . " - " . $brand_store_table_name;






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






// brands csv file, open and read
$brands_csv_file = $mage_root_path . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "brands.csv";
//echo $brands_csv_file;

$brands_csv_file_pointer = fopen($brands_csv_file, "r");

// Headrow
$head = fgetcsv($brands_csv_file_pointer, 4096);

// parse through brands and process

$processed_brands = array();
while (($row = fgetcsv($brands_csv_file_pointer, 4096)) !== false) {
    if (isset($row[0]) && $row[0] != "") {
        $brand_name = trim($row[0]);

        if ($brand_name != "") {
            if (!in_array($brand_name, $processed_brands)) {

                echo $brand_name;
//                echo PHP_EOL;


                // check if data is present in $brand_table_name
                $present = 1;

                try {
                    $check_brand_name = "SELECT brand_id FROM " . $brand_table_name . " WHERE name = :name";

                    $pdo_sth1 = $pdo_conn->prepare($check_brand_name);
                    $pdo_sth1->bindValue('name', $brand_name);
                    $pdo_sth1->execute();
                    $t_arr = $pdo_sth1->fetch(PDO::FETCH_ASSOC);

//                    print_r($t_arr);

                    if (isset($t_arr['brand_id']) && $t_arr['brand_id'] != "") {
                        echo ", present with id " . $t_arr['brand_id'] . ", skipping to next row";
                    } else {
                        echo ", NOT present, adding ";

                        $present = 0;
                    }
                } catch (PDOException $e) {
                    if (function_exists('pdo_error_handler')) {
                        pdo_error_handler($e, $pdo_sth1);
                    } else {
                        $trace = $e->getTrace();
                        echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
                    }
                }


                // add if not found
                $add_error = 0;
                if ($present == 0) {


                    // First, add to eav_attribute_option
                    try {
                        $add_eav_attribute_option = "INSERT INTO eav_attribute_option (attribute_id, sort_order) VALUES (:attribute_id, :sort_order)";

                        $pdo_sth_2 = $pdo_conn->prepare($add_eav_attribute_option);

                        $pdo_sth_2->bindValue('attribute_id', $attribute_id);
                        $pdo_sth_2->bindValue('sort_order', 0);

                        $pdo_sth_2->execute();
                    } catch (PDOException $e) {
                        $add_error = 1;
                        if (function_exists('pdo_error_handler')) {
                            pdo_error_handler($e, $pdo_sth);
                        } else {
                            $trace = $e->getTrace();
                            echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
                        }
                        die("Exit");
                    }
                    $option_id = $pdo_conn->lastInsertId();



                    // Second, add to eav_attribute_option_value
                    try {
                        $add_eav_attribute_option_value = "INSERT INTO eav_attribute_option_value (option_id, store_id, value) VALUES (:option_id, :store_id, :value)";

                        $pdo_sth_2 = $pdo_conn->prepare($add_eav_attribute_option_value);

                        $pdo_sth_2->bindValue('option_id', $option_id);
                        $pdo_sth_2->bindValue('store_id', 0);
                        $pdo_sth_2->bindValue('value', $brand_name);

                        $pdo_sth_2->execute();
                    } catch (PDOException $e) {
                        $add_error = 1;
                        if (function_exists('pdo_error_handler')) {
                            pdo_error_handler($e, $pdo_sth);
                        } else {
                            $trace = $e->getTrace();
                            echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
                        }
                        die("Exit");
                    }



                    // Third, add to brand table
                    try {
                        $add_brand_name = "INSERT INTO " . $brand_table_name . " (name, url_key, small_image, image, description, meta_keywords, meta_description, status, is_featured, sort_order, option_id) VALUES (:name, :url_key, :small_image, :image, :description, :meta_keywords, :meta_description, :status, :is_featured, :sort_order, :option_id)";

                        $pdo_sth_2 = $pdo_conn->prepare($add_brand_name);

                        $pdo_sth_2->bindValue('name', $brand_name);
                        $pdo_sth_2->bindValue('url_key', slugify($brand_name));
                        $pdo_sth_2->bindValue('small_image', NULL);
                        $pdo_sth_2->bindValue('image', NULL);
                        $pdo_sth_2->bindValue('description', NULL);
                        $pdo_sth_2->bindValue('meta_keywords', NULL);
                        $pdo_sth_2->bindValue('meta_description', NULL);
                        $pdo_sth_2->bindValue('status', 1);
                        $pdo_sth_2->bindValue('is_featured', 0);
                        $pdo_sth_2->bindValue('sort_order', 0);
                        $pdo_sth_2->bindValue('option_id', $option_id);

                        $pdo_sth_2->execute();
                    } catch (PDOException $e) {
                        $add_error = 1;
                        if (function_exists('pdo_error_handler')) {
                            pdo_error_handler($e, $pdo_sth);
                        } else {
                            $trace = $e->getTrace();
                            echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
                        }
                        exit;
                    }


                    $add_brand_store_error = 0;
                    if ($add_error == 0) {

                        // add into $brand_store_table_name

                        $brand_id = $pdo_conn->lastInsertId();

                        try {
                            $add_brand_store = "INSERT INTO " . $brand_store_table_name . " (brand_id, store_id) VALUES (:brand_id, :store_id)";

                            $pdo_sth_3 = $pdo_conn->prepare($add_brand_store);

                            $pdo_sth_3->bindValue('brand_id', $brand_id);
                            $pdo_sth_3->bindValue('store_id', 0);

                            $pdo_sth_3->execute();
                        } catch (PDOException $e) {
                            $add_brand_store_error = 1;
                            if (function_exists('pdo_error_handler')) {
                                pdo_error_handler($e, $pdo_sth);
                            } else {
                                $trace = $e->getTrace();
                                echo "PDO Error at FILE: " . $trace[0]['file'] . " at LINE: " . $trace[0]['line'] . ". Error: " . $e->getMessage() . PHP_EOL;
                            }
                        }

                    }

                    if ($add_brand_store_error == 0) {
                        echo " added with id: " . $brand_id;
                    }

                }

                echo PHP_EOL;


                $processed_brands[] = $brand_name;
            }
        }

    }
}

fclose($brands_csv_file_pointer);


// local functions
function slugify($text, $use = 1)
{
    // from https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string

    if ($use == 1) {
        // method 1
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
    }


    if ($use == 2) {
        // method 2
        $text = \Transliterator::createFromRules(
            ':: Any-Latin;'
            . ':: NFD;'
            . ':: [:Nonspacing Mark:] Remove;'
            . ':: NFC;'
            . ':: [:Punctuation:] Remove;'
            . ':: Lower();'
            . '[:Separator:] > \'-\''
        )
            ->transliterate($text);
    }


    if ($use == 3) {
        // method 3
        $text = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }


    return $text;
}

