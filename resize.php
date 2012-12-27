<?PHP

$files = find('.', '*.rrd');

//print_r($files);

foreach($files as $id => $file_path) {
  $info = rrd_info($file_path);
  	//The 2 is the RRA number (so the ID of the RRDB you want to update)
  	//use rrdtool info to view RRD File meta-data and update as needed
	$row_count = $info['rra[2].rows'];
	if($row_count == 9216) {

		$ix = strrpos($file_path, '/');
		$path = substr($file_path, 0, $ix);
		$file_name = substr($file_path, $ix + 1);
		echo  "row count => $row_count ($path => $file_name)\n";
		$target_rows = 18432; //64 days @ 5min resolution
		$row_diff = $target_rows - $row_count;

		if($row_diff > 0) {
			$command = "rrdtool resize $file_path 2 GROW $row_diff";
			exec($command);
			echo $command . "\n";

			$new_file =  './resize.rrd';
			$moved_old_file = $path . '/' . $file_name . '_old';

			echo "rename($file_path, $moved_old_file)\n";  //Move current RRD To old name
			rename($file_path, $moved_old_file);
			echo "rename($new_file,  $file_path)\n";        //Move new bigger RRD to the old file path
			rename($new_file,  $file_path);
			chmod($file_path, 0644); //File created with 600 permissions, update so that APACHE can read
		} 
		//die;
	}
}


//Foreach fileß
// Do resize
// get rrd info
// if size of rra-num 2 != 18432
// Grow RRD file
// move old file to _old
// move resize to old file name

/**
 * find files matching a pattern
 * using unix "find" command
 *
 * @return array containing all pattern-matched files
 *
 * @param string $dir     - directory to start with
 * @param string $pattern - pattern to search
 */
function find($dir, $pattern){
    // escape any character in a string that might be used to trick
    // a shell command into executing arbitrary commands
    $dir = escapeshellcmd($dir);
    // execute "find" and return string with found files
    $files = shell_exec("find $dir -name '$pattern' -print");
    // create array from the returned string (trim will strip the last newline)
    $files = explode("\n", trim($files));
    // return array
    return $files;
}


?>
