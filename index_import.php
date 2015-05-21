<?php
# http://www.gab.lc/articles/from_mysql_to_postgresql
# A very simple PHP tool to detect all the indexes from our MySQL schemas 
# and re-create them on PostgreSQL: on PostgreSQL, unlike with MySQL, you can't create 
# indexes in the CREATE TABLE syntax (see PostgreSQL and MySQL documentations).

$struct = file_get_contents('./struct.sql');

$tab = explode("\n", $struct);

$db = $tb = $index = "";

foreach($tab as $key => $value) {
	$value = trim($value);
	
	// db name
	if(substr($value, 0, 3) == "USE") {
		$db = substr($value, strpos($value, "`") + 1, strrpos($value, "`") - strpos($value, "`") - 1);
		echo "<br />\c ".$db."<br />";
	}
	
	// table name
	if(substr($value, 0, 12) == "CREATE TABLE") {
		$tb = substr($value, strpos($value, "`") + 1, strrpos($value, "`") - strpos($value, "`") - 1);
	}
	
	// unique key
	if(substr($value, 0, 10) == "UNIQUE KEY") {
		$index = substr($value, strpos($value, "`") + 1, strpos($value, "`", 12) - strpos($value, "`") - 1);
		
		echo "CREATE UNIQUE INDEX ".$tb."_".$index."_idx ON ".$tb." (".$index.");<br />";
	}
	
	// key
	if(substr($value, 0, 3) == "KEY") {
		$index = substr($value, strpos($value, "`") + 1, strpos($value, "`", 5) - strpos($value, "`") - 1);
	
		echo "CREATE INDEX ".$tb."_".$index."_idx ON ".$tb." (".$index.");<br />";
	}
}
?>
