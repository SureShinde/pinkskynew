<?php
// phpinfo();
print_r(get_loaded_extensions());
echo "<br>".phpversion();
/*
* This file was developed by Bhavin Shah and It was released under General Public Licence.
* Author : Bhavin Shah (Magento Ecommerce Certified Developer)
* Email : bhavinshah.sbs1@gmail.com
*/

ini_set('max_execution_time', 3000000);
ini_set('memory_limit', '1G');

function cleandir($dir) {
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && is_file($dir . '/' . $file)) {
				if (unlink($dir . '/' . $file)) {
					
				} else {
					echo $dir . '/' . $file . ' (file) NOT deleted!<br />';
				}
			} else if ($file != '.' && $file != '..' && is_dir($dir . '/' . $file)) {
				cleandir($dir . '/' . $file);
				if (rmdir($dir . '/' . $file)) {
					
				} else {
					echo $dir . '/' . $file . ' (directory) NOT deleted!<br />';
				}
			}
		}
		closedir($handle);
	}
}
//cleandir("var/cache");

function listFolderFiles($dir) {
	$ffs = scandir($dir);
	foreach($ffs as $ff){
		if($ff != '.' && $ff != '..'){
			if(is_dir($dir.'/'.$ff)) {
				listFolderFiles($dir.'/'.$ff);
			} else {
				$file = $dir. '/' .$ff;
				findWord($file,$_POST['word']);
			}
		}
	}
}

function findWord($file,$word) {
	$search      = $word;
	$lines       = file($file);
	$line_numbers = array();
	$line_number = 0;
	while (list($key, $line) = each($lines)) {
		$line_number = (strpos($line, $search) !== FALSE) ? $key + 1 : 0;
		if($line_number){
			$line_numbers[] = $line_number;
		}
	}
	if(count($line_numbers)) {
		echo $file.'<br/>';
		$line_numbers = implode(',', $line_numbers);
		echo '<strong>'.$line_numbers.'</strong><br/>';
	}
}
?>
<form method="post">
	<b>BASE-PATH : </b><?php echo dirname(__FILE__) ?><input type="checkbox" name="use" checked><br/>
	<input type="text" name="search" value="" placeholder="please enter path..." /><br/>
	<input type="text" name="word" value="" placeholder="please enter word..." />
	<button type="submit" name="submit">Submit</button>
</form>

<?php
if(isset($_POST['submit'])){
	if(isset($_POST['use'])) {
		$dir = dirname(__FILE__) . '/' . $_POST['search'];
	} else {
		$dir = $_POST['search'];
	}

	if(is_dir($dir)) {
		echo "Searching in <b>". $dir. "</b>";
		echo "<br/>Searching for <b>". $_POST['word']. "</b> word<br/><br/>";
		listFolderFiles($dir);
		echo '<br/>Done';
	} else {
		echo "<b>No directory found.</b>";
	}
}
