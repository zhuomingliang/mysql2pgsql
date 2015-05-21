<?php
/*
Created by Gabriel Bordeaux / http://www.gab.lc
Version 1.0 - December 18th 2012
Version 1.1 - August 21st 2013
Version 1.2 - February 6th 2014

Important infos :
=> I created this program for my personnal use, there is absolutely no warranty that it'll work in your configuration/with your databases.
=> Please backup your MySQL Table before to try this script
=> If the tables exists in the PostgreSQL database, they'll be truncated => be carrefull if you want to keep the original datas
=> You need to launch the PHP program from a CLI (mainly because of PHP timeouts).

Howto :
=> Fill the MySQL and PostgreSQL connection infos below
=> Then just run "php my2pg.php" from your termunal
*/

// Memory limit
ini_set("memory_limit","1024M");
ini_set('error_reporting', E_ALL ^ E_NOTICE);
// ini_set('error_reporting', E_ALL);

// MySQL connection
$MyHost = "localhost";
$MyUser = "gab_login";
$MyPass = "gab_password";

// PostgreSQL connection
$PgHost = "localhost";
$PgUser = "gab_login";
$PgPass = "gab_password";
$PgDb = false; // if "false", the name of the Db will be the same than the name in MySQL.
			   // If you want to force a different name for the Db in pg, just put it there, for example :
			   // $PgDb = "my_db_test";
$PgEncoding = "UTF8"; // LATIN1, UTF8...

// Default Db for PG
// Pg need a default Db for the initial connexion
// By default we will use $PgDb if specified
// Else we will try your login
// If you get an error like "PHP Warning:  pg_connect(): Unable to connect to PostgreSQL server: FATAL:  database "XXX" does not exist"
// Just add the name of an existant PG database here
if($PgDb) {
	$PgDbDefault = $PgDb;
} else {
	$PgDbDefault = $PgUser;
}

// Retrieve limit
$RetrieveLimit = 10000; // How many lines should we retrieve from a table at a time => Limit the memory used by the script on your server

// SQL key words (reserved words)
// See http://www.postgresql.org/docs/9.4/static/sql-keywords-appendix.html
$SqlKeyWords = Array('A', 'ABORT', 'ABS', 'ABSENT', 'ABSOLUTE', 'ACCESS', 'ACCORDING', 'ACTION', 'ADA', 'ADD', 'ADMIN', 'AFTER', 'AGGREGATE', 'ALL', 'ALLOCATE', 'ALSO', 'ALTER', 'ALWAYS', 'ANALYSE', 'ANALYZE', 'AND', 'ANY', 'ARE', 'ARRAY', 'ARRAY_AGG', 'ARRAY_MAX_CARDINALITY', 'AS', 'ASC', 'ASENSITIVE', 'ASSERTION', 'ASSIGNMENT', 'ASYMMETRIC', 'AT', 'ATOMIC', 'ATTRIBUTE', 'ATTRIBUTES', 'AUTHORIZATION', 'AVG', 'BACKWARD', 'BASE64', 'BEFORE', 'BEGIN', 'BEGIN_FRAME', 'BEGIN_PARTITION', 'BERNOULLI', 'BETWEEN', 'BIGINT', 'BINARY', 'BIT', 'BIT_LENGTH', 'BLOB', 'BLOCKED', 'BOM', 'BOOLEAN', 'BOTH', 'BREADTH', 'BY', 'C', 'CACHE', 'CALL', 'CALLED', 'CARDINALITY', 'CASCADE', 'CASCADED', 'CASE', 'CAST', 'CATALOG', 'CATALOG_NAME', 'CEIL', 'CEILING', 'CHAIN', 'CHAR', 'CHARACTER', 'CHARACTERISTICS', 'CHARACTERS', 'CHARACTER_LENGTH', 'CHARACTER_SET_CATALOG', 'CHARACTER_SET_NAME', 'CHARACTER_SET_SCHEMA', 'CHAR_LENGTH', 'CHECK', 'CHECKPOINT', 'CLASS', 'CLASS_ORIGIN', 'CLOB', 'CLOSE', 'CLUSTER', 'COALESCE', 'COBOL', 'COLLATE', 'COLLATION', 'COLLATION_CATALOG', 'COLLATION_NAME', 'COLLATION_SCHEMA', 'COLLECT', 'COLUMN', 'COLUMNS', 'COLUMN_NAME', 'COMMAND_FUNCTION', 'COMMAND_FUNCTION_CODE', 'COMMENT', 'COMMENTS', 'COMMIT', 'COMMITTED', 'CONCURRENTLY', 'CONDITION', 'CONDITION_NUMBER', 'CONFIGURATION', 'CONNECT', 'CONNECTION', 'CONNECTION_NAME', 'CONSTRAINT', 'CONSTRAINTS', 'CONSTRAINT_CATALOG', 'CONSTRAINT_NAME', 'CONSTRAINT_SCHEMA', 'CONSTRUCTOR', 'CONTAINS', 'CONTENT', 'CONTINUE', 'CONTROL', 'CONVERSION', 'CONVERT', 'COPY', 'CORR', 'CORRESPONDING', 'COST', 'COUNT', 'COVAR_POP', 'COVAR_SAMP', 'CREATE', 'CROSS', 'CSV', 'CUBE', 'CUME_DIST', 'CURRENT', 'CURRENT_CATALOG', 'CURRENT_DATE', 'CURRENT_DEFAULT_TRANSFORM_GROUP', 'CURRENT_PATH', 'CURRENT_ROLE', 'CURRENT_ROW', 'CURRENT_SCHEMA', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_TRANSFORM_GROUP_FOR_TYPE', 'CURRENT_USER', 'CURSOR', 'CURSOR_NAME', 'CYCLE', 'DATA', 'DATABASE', 'DATALINK', 'DATE', 'DATETIME_INTERVAL_CODE', 'DATETIME_INTERVAL_PRECISION', 'DAY', 'DB', 'DEALLOCATE', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DEFAULTS', 'DEFERRABLE', 'DEFERRED', 'DEFINED', 'DEFINER', 'DEGREE', 'DELETE', 'DELIMITER', 'DELIMITERS', 'DENSE_RANK', 'DEPTH', 'DEREF', 'DERIVED', 'DESC', 'DESCRIBE', 'DESCRIPTOR', 'DETERMINISTIC', 'DIAGNOSTICS', 'DICTIONARY', 'DISABLE', 'DISCARD', 'DISCONNECT', 'DISPATCH', 'DISTINCT', 'DLNEWCOPY', 'DLPREVIOUSCOPY', 'DLURLCOMPLETE', 'DLURLCOMPLETEONLY', 'DLURLCOMPLETEWRITE', 'DLURLPATH', 'DLURLPATHONLY', 'DLURLPATHWRITE', 'DLURLSCHEME', 'DLURLSERVER', 'DLVALUE', 'DO', 'DOCUMENT', 'DOMAIN', 'DOUBLE', 'DROP', 'DYNAMIC', 'DYNAMIC_FUNCTION', 'DYNAMIC_FUNCTION_CODE', 'EACH', 'ELEMENT', 'ELSE', 'EMPTY', 'ENABLE', 'ENCODING', 'ENCRYPTED', 'END', 'END-EXEC', 'END_FRAME', 'END_PARTITION', 'ENFORCED', 'ENUM', 'EQUALS', 'ESCAPE', 'EVENT', 'EVERY', 'EXCEPT', 'EXCEPTION', 'EXCLUDE', 'EXCLUDING', 'EXCLUSIVE', 'EXEC', 'EXECUTE', 'EXISTS', 'EXP', 'EXPLAIN', 'EXPRESSION', 'EXTENSION', 'EXTERNAL', 'EXTRACT', 'FALSE', 'FAMILY', 'FETCH', 'FILE', 'FILTER', 'FINAL', 'FIRST', 'FIRST_VALUE', 'FLAG', 'FLOAT', 'FLOOR', 'FOLLOWING', 'FOR', 'FORCE', 'FOREIGN', 'FORTRAN', 'FORWARD', 'FOUND', 'FRAME_ROW', 'FREE', 'FREEZE', 'FROM', 'FS', 'FULL', 'FUNCTION', 'FUNCTIONS', 'FUSION', 'G', 'GENERAL', 'GENERATED', 'GET', 'GLOBAL', 'GO', 'GOTO', 'GRANT', 'GRANTED', 'GREATEST', 'GROUP', 'GROUPING', 'GROUPS', 'HANDLER', 'HAVING', 'HEADER', 'HEX', 'HIERARCHY', 'HOLD', 'HOUR', 'IDENTITY', 'IF', 'IGNORE', 'ILIKE', 'IMMEDIATE', 'IMMEDIATELY', 'IMMUTABLE', 'IMPLEMENTATION', 'IMPLICIT', 'IMPORT', 'IN', 'INCLUDING', 'INCREMENT', 'INDENT', 'INDEX', 'INDEXES', 'INDICATOR', 'INHERIT', 'INHERITS', 'INITIALLY', 'INLINE', 'INNER', 'INOUT', 'INPUT', 'INSENSITIVE', 'INSERT', 'INSTANCE', 'INSTANTIABLE', 'INSTEAD', 'INT', 'INTEGER', 'INTEGRITY', 'INTERSECT', 'INTERSECTION', 'INTERVAL', 'INTO', 'INVOKER', 'IS', 'ISNULL', 'ISOLATION', 'JOIN', 'K', 'KEY', 'KEY_MEMBER', 'KEY_TYPE', 'LABEL', 'LAG', 'LANGUAGE', 'LARGE', 'LAST', 'LAST_VALUE', 'LATERAL', 'LC_COLLATE', 'LC_CTYPE', 'LEAD', 'LEADING', 'LEAKPROOF', 'LEAST', 'LEFT', 'LENGTH', 'LEVEL', 'LIBRARY', 'LIKE', 'LIKE_REGEX', 'LIMIT', 'LINK', 'LISTEN', 'LN', 'LOAD', 'LOCAL', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATION', 'LOCATOR', 'LOCK', 'LOWER', 'M', 'MAP', 'MAPPING', 'MATCH', 'MATCHED', 'MATERIALIZED', 'MAX', 'MAXVALUE', 'MAX_CARDINALITY', 'MEMBER', 'MERGE', 'MESSAGE_LENGTH', 'MESSAGE_OCTET_LENGTH', 'MESSAGE_TEXT', 'METHOD', 'MIN', 'MINUTE', 'MINVALUE', 'MOD', 'MODE', 'MODIFIES', 'MODULE', 'MONTH', 'MORE', 'MOVE', 'MULTISET', 'MUMPS', 'NAME', 'NAMES', 'NAMESPACE', 'NATIONAL', 'NATURAL', 'NCHAR', 'NCLOB', 'NESTING', 'NEW', 'NEXT', 'NFC', 'NFD', 'NFKC', 'NFKD', 'NIL', 'NO', 'NONE', 'NORMALIZE', 'NORMALIZED', 'NOT', 'NOTHING', 'NOTIFY', 'NOTNULL', 'NOWAIT', 'NTH_VALUE', 'NTILE', 'NULL', 'NULLABLE', 'NULLIF', 'NULLS', 'NUMBER', 'NUMERIC', 'OBJECT', 'OCCURRENCES_REGEX', 'OCTETS', 'OCTET_LENGTH', 'OF', 'OFF', 'OFFSET', 'OIDS', 'OLD', 'ON', 'ONLY', 'OPEN', 'OPERATOR', 'OPTION', 'OPTIONS', 'OR', 'ORDER', 'ORDERING', 'ORDINALITY', 'OTHERS', 'OUT', 'OUTER', 'OUTPUT', 'OVER', 'OVERLAPS', 'OVERLAY', 'OVERRIDING', 'OWNED', 'OWNER', 'P', 'PAD', 'PARAMETER', 'PARAMETER_MODE', 'PARAMETER_NAME', 'PARAMETER_ORDINAL_POSITION', 'PARAMETER_SPECIFIC_CATALOG', 'PARAMETER_SPECIFIC_NAME', 'PARAMETER_SPECIFIC_SCHEMA', 'PARSER', 'PARTIAL', 'PARTITION', 'PASCAL', 'PASSING', 'PASSTHROUGH', 'PASSWORD', 'PATH', 'PERCENT', 'PERCENTILE_CONT', 'PERCENTILE_DISC', 'PERCENT_RANK', 'PERIOD', 'PERMISSION', 'PLACING', 'PLANS', 'PLI', 'PORTION', 'POSITION', 'POSITION_REGEX', 'POWER', 'PRECEDES', 'PRECEDING', 'PRECISION', 'PREPARE', 'PREPARED', 'PRESERVE', 'PRIMARY', 'PRIOR', 'PRIVILEGES', 'PROCEDURAL', 'PROCEDURE', 'PROGRAM', 'PUBLIC', 'QUOTE', 'RANGE', 'RANK', 'READ', 'READS', 'REAL', 'REASSIGN', 'RECHECK', 'RECOVERY', 'RECURSIVE', 'REF', 'REFERENCES', 'REFERENCING', 'REFRESH', 'REGR_AVGX', 'REGR_AVGY', 'REGR_COUNT', 'REGR_INTERCEPT', 'REGR_R2', 'REGR_SLOPE', 'REGR_SXX', 'REGR_SXY', 'REGR_SYY', 'REINDEX', 'RELATIVE', 'RELEASE', 'RENAME', 'REPEATABLE', 'REPLACE', 'REPLICA', 'REQUIRING', 'RESET', 'RESPECT', 'RESTART', 'RESTORE', 'RESTRICT', 'RESULT', 'RETURN', 'RETURNED_CARDINALITY', 'RETURNED_LENGTH', 'RETURNED_OCTET_LENGTH', 'RETURNED_SQLSTATE', 'RETURNING', 'RETURNS', 'REVOKE', 'RIGHT', 'ROLE', 'ROLLBACK', 'ROLLUP', 'ROUTINE', 'ROUTINE_CATALOG', 'ROUTINE_NAME', 'ROUTINE_SCHEMA', 'ROW', 'ROWS', 'ROW_COUNT', 'ROW_NUMBER', 'RULE', 'SAVEPOINT', 'SCALE', 'SCHEMA', 'SCHEMA_NAME', 'SCOPE', 'SCOPE_CATALOG', 'SCOPE_NAME', 'SCOPE_SCHEMA', 'SCROLL', 'SEARCH', 'SECOND', 'SECTION', 'SECURITY', 'SELECT', 'SELECTIVE', 'SELF', 'SENSITIVE', 'SEQUENCE', 'SEQUENCES', 'SERIALIZABLE', 'SERVER', 'SERVER_NAME', 'SESSION', 'SESSION_USER', 'SET', 'SETOF', 'SETS', 'SHARE', 'SHOW', 'SIMILAR', 'SIMPLE', 'SIZE', 'SMALLINT', 'SNAPSHOT', 'SOME', 'SOURCE', 'SPACE', 'SPECIFIC', 'SPECIFICTYPE', 'SPECIFIC_NAME', 'SQL', 'SQLCODE', 'SQLERROR', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQRT', 'STABLE', 'STANDALONE', 'START', 'STATE', 'STATEMENT', 'STATIC', 'STATISTICS', 'STDDEV_POP', 'STDDEV_SAMP', 'STDIN', 'STDOUT', 'STORAGE', 'STRICT', 'STRIP', 'STRUCTURE', 'STYLE', 'SUBCLASS_ORIGIN', 'SUBMULTISET', 'SUBSTRING', 'SUBSTRING_REGEX', 'SUCCEEDS', 'SUM', 'SYMMETRIC', 'SYSID', 'SYSTEM', 'SYSTEM_TIME', 'SYSTEM_USER', 'T', 'TABLE', 'TABLES', 'TABLESAMPLE', 'TABLESPACE', 'TABLE_NAME', 'TEMP', 'TEMPLATE', 'TEMPORARY', 'TEXT', 'THEN', 'TIES', 'TIME', 'TIMESTAMP', 'TIMEZONE_HOUR', 'TIMEZONE_MINUTE', 'TO', 'TOKEN', 'TOP_LEVEL_COUNT', 'TRAILING', 'TRANSACTION', 'TRANSACTIONS_COMMITTED', 'TRANSACTIONS_ROLLED_BACK', 'TRANSACTION_ACTIVE', 'TRANSFORM', 'TRANSFORMS', 'TRANSLATE', 'TRANSLATE_REGEX', 'TRANSLATION', 'TREAT', 'TRIGGER', 'TRIGGER_CATALOG', 'TRIGGER_NAME', 'TRIGGER_SCHEMA', 'TRIM', 'TRIM_ARRAY', 'TRUE', 'TRUNCATE', 'TRUSTED', 'TYPE', 'TYPES', 'UESCAPE', 'UNBOUNDED', 'UNCOMMITTED', 'UNDER', 'UNENCRYPTED', 'UNION', 'UNIQUE', 'UNKNOWN', 'UNLINK', 'UNLISTEN', 'UNLOGGED', 'UNNAMED', 'UNNEST', 'UNTIL', 'UNTYPED', 'UPDATE', 'UPPER', 'URI', 'USAGE', 'USER', 'USER_DEFINED_TYPE_CATALOG', 'USER_DEFINED_TYPE_CODE', 'USER_DEFINED_TYPE_NAME', 'USER_DEFINED_TYPE_SCHEMA', 'USING', 'VACUUM', 'VALID', 'VALIDATE', 'VALIDATOR', 'VALUE', 'VALUES', 'VALUE_OF', 'VARBINARY', 'VARCHAR', 'VARIADIC', 'VARYING', 'VAR_POP', 'VAR_SAMP', 'VERBOSE', 'VERSION', 'VERSIONING', 'VIEW', 'VOLATILE', 'WHEN', 'WHENEVER', 'WHERE', 'WHITESPACE', 'WIDTH_BUCKET', 'WINDOW', 'WITH', 'WITHIN', 'WITHOUT', 'WORK', 'WRAPPER', 'WRITE', 'XML', 'XMLAGG', 'XMLATTRIBUTES', 'XMLBINARY', 'XMLCAST', 'XMLCOMMENT', 'XMLCONCAT', 'XMLDECLARATION', 'XMLDOCUMENT', 'XMLELEMENT', 'XMLEXISTS', 'XMLFOREST', 'XMLITERATE', 'XMLNAMESPACES', 'XMLPARSE', 'XMLPI', 'XMLQUERY', 'XMLROOT', 'XMLSCHEMA', 'XMLSERIALIZE', 'XMLTABLE', 'XMLTEXT', 'XMLVALIDATE', 'YEAR', 'YES', 'ZONE');

// Data types
// See http://www.postgresql.org/docs/9.4/static/datatype.html
$DataTypes = Array('bigint', 'bigserial', 'bit ', 'bit varying ', 'boolean', 'box', 'bytea', 'character ', 'character varying', 'cidr', 'circle', 'date', 'double precision', 'inet', 'integer', 'interval', 'json', 'line', 'lseg', 'macaddr', 'money', 'numeric', 'path', 'point', 'polygon', 'real', 'smallint', 'smallserial', 'serial', 'text', 'time', 'timestamp', 'tsquery', 'tsvector', 'txid_snapshot', 'uuid', 'xml', 'int8', 'serial8', 'varbit', 'bool', 'char', 'varchar', 'float8', 'int', 'int4', 'decimal', 'float4', 'int2', 'serial2', 'serial4', 'timetz', 'timestamptz');

// Vars
$db = $argv[1];

// MySQL connection
echo "* MySQL connection...\n";
$MyConn = mysql_connect($MyHost, $MyUser, $MyPass);
if(!$MyConn) {
	echo "* Error: Mysql connection impossible";
	echo "* EXITING! (sorry about that)\n";
	exit();
}

// PostgreSQL connection
echo "* PostgreSQL connection...\n";
$PgConn = pg_connect("host=".$PgHost." port=5432 user=".$PgUser." password=".$PgPass." dbname=".$PgDbDefault);
if(!$PgConn) {
	echo "* Error: PostgreSQL connection impossible\n";
	echo "* EXITING! (sorry about that)\n";
	exit();
}

// MySQL UTF-8 Management
if($PgEncoding == "UTF8") {
	mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $MyConn);
}

if(!$db) { // The user did not ask to import any db
	// List of MySQL databases
	echo "* List of MySQL databases...\n";
	$res = mysql_query("SHOW DATABASES");
	while ($row = mysql_fetch_assoc($res)) {
		if($row['Database'] != "information_schema" && $row['Database'] != "mysql") {
			echo "** ".$row['Database']." => to import this db, call: php ".$_SERVER['SCRIPT_NAME']." ".$row['Database']."\n";
		}
	}
} else { // The user asked to import a specific db
	// Db selection
	echo "* Selection of MySQL database \"".$db."\"...\n";
	if(!mysql_select_db($db)) {
		echo "* Error: Impossible to select MySQL database \"".$db."\"\n";
		echo "* EXITING! (sorry about that)\n";
		exit();
	}
	
	// Name of the Db in PG if not specified
	if($PgDb == false) { $PgDb = $db; }
	
	// Does the db exists in pg ?
	$res = pg_query($PgConn, "SELECT 1 from pg_database WHERE datname='".$PgDb."'");
	$numrows = pg_numrows($res);
	if($numrows == 0) {
		// Pg db creation
		echo "* Creation of the PostgreSQL database \"".$PgDb."\"...\n";
		pg_query($PgConn, "CREATE DATABASE ".$PgDb." ENCODING '".$PgEncoding."';");
	}
	
	echo "* Connection of the PostgreSQL database \"".$PgDb."\"...\n";
	pg_close();
	$PgConn = pg_connect("host=".$PgHost." port=5432  dbname=".$PgDb." user=".$PgUser." password=".$PgPass);
	if(!$PgConn) {
		echo "* Error: PostgreSQL connection to the database \"".$PgDb."\" impossible.\n";
		echo "* EXITING! (sorry about that)\n";
		exit();
	}
	
	echo "* List of MySQL tables in \"".$db."\"...\n";
	$res_tables = mysql_query("SHOW TABLES FROM ".$db, $MyConn);
	if (!$res_tables) {
		echo "* Error: Impossible to list MySQL tables for the database \"".$db."\"\n";
		echo "* EXITING! (sorry about that)\n";
		exit();
	}
	while ($row_tables = mysql_fetch_row($res_tables)) { // List of tables
		// Vars
		$skip = false; // Will be "true" if we skip the table later
		$table = $row_tables[0]; // Table Name
		
		// Here is a little example on how to proceed to skip a specific table
		/*
		if($table == "log_actions") {
			$skip = true;
		}
		*/
		
		echo "...\n";
		echo "** Table \"".$table."\"\n";
		
		if($skip == false) { // Exept if we skip the table!
			// Check the number of entries in MySQL
			$res_num = mysql_query("SELECT count(*) as nb FROM ".$table, $MyConn);
			$MyNumEntries = mysql_fetch_assoc($res_num);
			echo "*** There is ".number_format($MyNumEntries['nb'], 0)." entries in this MySQL table.\n";
			
			// Check if the table exists in PostgreSQL
			$res = pg_query($PgConn, "SELECT 1 FROM information_schema.tables WHERE table_name = '".$table."'");
			$numrows = pg_numrows($res);
			if($numrows == 0) {
				echo "*** The table does not exists in the PostgreSQL db.\n";
				
				// We get the schema from MySQL
				$res_schema = mysql_query("SHOW CREATE TABLE ".$table, $MyConn);
				$tab_schema = mysql_fetch_assoc($res_schema);
				$MySchema = $tab_schema['Create Table'];
				
				// Conversion
				$PgSchema = $MySchema;
				$PgSchema = str_replace("`", "", $PgSchema); // Removes the "`" that pg does not like
				$PgSchema = str_replace(" unsigned", "", $PgSchema); // The type "UNSIGNED" is not available in PG
				$PgSchema = str_replace(" zerofill", "", $PgSchema); // The type "ZEROFILL" is not available in PG
				$PgSchema = str_replace("  ", "", $PgSchema); // Remove double spaces
				$PgSchema = str_replace("int(4) unsigned zerofill", "bigint", $PgSchema); // Specific replacement for ip2location
				$PgSchema = str_replace("int(16) unsigned", "bigint", $PgSchema); // Specific replacement for ip2location
				$PgSchema = str_replace("datetime", "timestamp", $PgSchema); // Convert the type "datetime" to "timestamp"
				$PgSchema = preg_replace("/bigint\([0-9]+\)/i", "bigint", $PgSchema); // Adapt the type "bigint"
				$PgSchema = preg_replace("/int\([0-9]+\)/i", "integer", $PgSchema); // Adapt the type "int"
				$PgSchema = preg_replace("/smallinteger\([0-9]+\)/i", "smallint", $PgSchema); // Adapt the type "smallint"
				$PgSchema = str_replace("smallinteger", "smallint", $PgSchema); // Adapt the type "smallint"
				$PgSchema = str_replace("tinyinteger", "smallint", $PgSchema); // Adapt the type "tinyinteger"
				$PgSchema = str_replace("double(", "decimal(", $PgSchema); // Adapt the type "double"
				$PgSchema = str_replace("mediumtext", "TEXT", $PgSchema); // Adapt the type "mediumtext"
				$PgSchema = str_replace("tinyblob", "BYTEA", $PgSchema); // Adapt the type "tinyblob"
				$PgSchema = str_replace("blob", "BYTEA", $PgSchema); // Adapt the type "blob"
				$PgSchema = str_replace("mediumblob", "BYTEA", $PgSchema); // Adapt the type "mediumblob"
				$PgSchema = str_replace("longblob", "BYTEA", $PgSchema); // Adapt the type "longblob"
				$PgSchema = str_replace("tinytext", "TEXT", $PgSchema); // Adapt the type "tinytext"
				$PgSchema = str_replace("mediumtext", "TEXT", $PgSchema); // Adapt the type "mediumtext"
				$PgSchema = str_replace("longtext", "TEXT", $PgSchema); // Adapt the type "longtext"
				$PgSchema = preg_replace("/ENGINE=[0-9a-z]+/i", "", $PgSchema); // Remove the engine type
				$PgSchema = preg_replace("/DEFAULT CHARSET=[0-9a-z]+/i", "", $PgSchema); // Remove the default charset
				$PgSchema = preg_replace("/ROW_FORMAT=[0-9a-z]+/i", "", $PgSchema); // Remove the row format function
				$PgSchema = preg_replace("/AUTO_INCREMENT=[0-9a-z]+/i", "", $PgSchema); // Remove the id first value
				$PgSchema = preg_replace("/PACK_KEYS=[0-9a-z]+/i", "", $PgSchema); // Remove the PACK_KEYS value
				$PgSchema = preg_replace("/ON UPDATE[0-9a-zA-Z ()_]+/i", "", $PgSchema); // Remove the "ON UPDATE" info, you'll need a trigger for that in PG
				$PgSchema = preg_replace("/(int|integer|bigint|smallint|tinyint|mediumint) NOT NULL AUTO_INCREMENT/i", "SERIAL NOT NULL", $PgSchema); // Remove the indexes except the primary key
				$PgSchema = preg_replace("/(int|integer|bigint|smallint|tinyint|mediumint) AUTO_INCREMENT/i", "SERIAL", $PgSchema); // Remove the indexes except the primary key
				$PgSchema = preg_replace("/(int|integer|bigint|smallint|tinyint|mediumint) SERIAL/i", "SERIAL", $PgSchema); // Remove the indexes except the primary key
				$PgSchema = str_replace("UNIQUE KEY", "KEY", $PgSchema); // Transformation of "UNIQUE KEY" to "KEY"
				$PgSchema = preg_replace("/[^PRIMARY] Key [0-9a-z,() _]+/i", "", $PgSchema); // Remove the indexes except the primary key
				$PgSchema = preg_replace("/,[ \n]+\)/", "\n)", $PgSchema); // Correct the syntax in case the query end by ", );"
				$PgSchema = str_replace("DEFAULT '0000-00-00 00:00:00'", "DEFAULT NOW()", $PgSchema); // PG does not permit null dates/times
				$PgSchema = str_replace("DEFAULT '0000-00-00'", "DEFAULT current_date", $PgSchema); // PG does not permit null dates
				$PgSchema = str_replace("CURRENT_TIMESTAMP", "NOW()", $PgSchema); // Timestamp conversion
				
				// Check for reserver words in column names
				$tab = explode("\n", $PgSchema);
				$PgSchema = ""; // empty the schema
				foreach($tab as $line) {
					$words = explode(' ',trim($line)); // get all words
					$first_word = $words[0]; // get the first word (the column name)
					
					// If there is a word, if the line contains a data type and if the word matched a reserved word
					if(trim($first_word) && strposa($line, $DataTypes, 1) && in_array(strtolower($first_word), array_map('strtolower', $SqlKeyWords))) {
						// we will quote the reserved word
						$line = preg_replace("/".preg_quote($first_word)."/", "\"".preg_quote($first_word)."\"", $line, 1);
					}
					
					// Add the line to the schema
					$PgSchema .= $line."\n";
				}

				/*
				// If you want to debug a Schema without trying to create it in pg
				echo $PgSchema."\n\n";
				exit();
				*/
				
				// Creation of the table in pg
				echo "*** Creation of the table in PostgreSQL...\n";
				$res_creation = pg_query($PgConn, $PgSchema);
				$error = pg_last_error($PgConn);
				if($error) {
					echo "*** ...The table could not be created.\n";
					echo "*** ...Please try to create the table manually then re-run the script.\n";
					echo "*** ...Original schema (MySQL):\n";
					sleep(2);
					echo $MySchema."\n\n";
					echo "*** ...Converted schema (PostgreSQL):\n";
					sleep(2);
					echo $PgSchema."\n\n";
					echo "*** ...Error returned from PostgreSQL:\n";
					sleep(2);
					echo $error."\n\n";
					echo "* EXITING! (sorry about that)\n";
					exit();
				}
			} else {
				echo "*** The table exists in the PostgreSQL db...\n";
				
				// Number of entries in the Pg table
				$res_num = pg_query($PgConn, "SELECT count(*) as nb FROM ".$table);
				$PgNumEntries = pg_fetch_array($res_num, null, PGSQL_ASSOC);
				echo "*** There is ".number_format($PgNumEntries['nb'], 0)." entries in the PostgreSQL table.\n";
				
				// Comparaison of number of entries
				if($MyNumEntries['nb'] == $PgNumEntries['nb']) {
					echo "*** ...the tables seems identical!\n";
					echo "*** ...we SKIP this table!\n";
					$skip = true;
				} else {
					echo "*** Truncate of the PostgreSQL table...\n";
					pg_query($PgConn, "TRUNCATE TABLE ".$table);
					$error = pg_last_error($PgConn);
					if($error) {
						echo "*** ...Error returned from PostgreSQL:\n";
						echo $error."\n\n";
						echo "*** Please try manually then re-launch the script.\n";
						echo "* EXITING! (sorry about that)\n";
						exit();
					}
				}
			}
		} else { // Skip request
			echo "*** ...we SKIP this table!\n";
		}
		
		// Let's get the datas
		if($skip == false) { // Exept if we skip the table!
			// For how many entries should we verbose ?
			if($MyNumEntries['nb'] >     100000000) { $verbose = 10000000; }
			elseif($MyNumEntries['nb'] > 10000000)  { $verbose = 1000000; }
			elseif($MyNumEntries['nb'] > 1000000)   { $verbose = 100000; }
			elseif($MyNumEntries['nb'] > 100000)    { $verbose = 10000; }
			elseif($MyNumEntries['nb'] > 10000)     { $verbose = 1000; }
			elseif($MyNumEntries['nb'] > 1000)      { $verbose = 100; }
			else { $verbose = 0; } // No verbose
		
			// Number of lines
			echo "*** There is ".number_format($MyNumEntries['nb'], 0)." entries to import...\n";
			
			for ($i_retrieve = 0; $i_retrieve <= $MyNumEntries['nb']; $i_retrieve = $i_retrieve + $RetrieveLimit) {
				if($i_retrieve == 0) {
					echo "*** Retrieve the content from the MySQL table...\n";
				} else {
					echo "*** Retrieve more content from the MySQL table to limit memory usage...\n";
				}
				$MyDatas = mysql_query("SELECT * FROM ".$table." LIMIT ".$i_retrieve.", ".$RetrieveLimit, $MyConn);

				if($i_retrieve == 0) {
					echo "*** Starting import entries from \"".$table."\"...\n";
				}
				
				// Retrieve each entry
				$numr = mysql_num_rows($MyDatas);
				$numf = mysql_num_fields($MyDatas);
				for($i = 0; $i < $numr; $i++)
				{
					$fields_txt = "";
					$values_txt = "";
				
					for($j = 0; $j < $numf; $j++)
					{
				    	$infofields = mysql_fetch_field($MyDatas, $j);
				    	$field = $infofields->name;
				    	$value = mysql_result($MyDatas, $i, $field);
				    	
				    	// Value
				    	if($value === NULL) { // NULL value
					    	$value = "NULL";
				    	} else {
					    	$value = str_replace("`", "'", $value); // Replacing the "`" that pg does not like
					    	$value = str_replace("´", "'", $value); // Replacing the "´" that pg does not like
					    	$value = stripslashes($value);
					    	
							// Automaticly change the encoding if needed
							if($PgEncoding == "UTF8" && !mb_detect_encoding($$value, 'UTF-8', true)) 
							{ 
								$value = utf8_encode($value);
							}

					    	$value = pg_escape_string($value);
					    	$value = "'".$value."'";
				    	}
				    	
				    	// Corrections
				    	if($value == "'0000-00-00 00:00:00'") { $value = "'1970-01-01 00:00:00'"; } // PG does not permit null dates/times
				    	elseif($value == "'0000-00-00'") { $value = "'1970-01-01'"; } // PG does not permit null dates
	
				    	$fields_txt .= $field.", ";
				    	$values_txt .= $value.", ";
					}
					
					// Trim
					$fields_txt = trim($fields_txt, ", ");
					$values_txt = trim($values_txt, ", ");
					
					// Insert
					$insert_into_pg = "INSERT INTO ".$table." (".$fields_txt.") VALUES (".$values_txt.");\n\n";
					pg_query($PgConn, $insert_into_pg);
					$error = pg_last_error($PgConn);
					if($error) {
						echo "*** ...Error returned from PostgreSQL:\n";
						echo $error."\n\n";
						echo "*** ...The query was:\n";
						echo $insert_into_pg."\n\n";
						echo "*** Please fix this entry in the MySQL table then re-launch the script.\n";
						echo "* EXITING! (sorry about that)\n";
						exit();
					}
					
					// Verbose
					if($verbose != 0 && $i + $i_retrieve != 0 && is_int($i / $verbose)) {
						echo "*** ...".number_format($i + $i_retrieve, 0)." lines inserted in \"".$table."\"\n";
					}
				}
			}
			
			// Number of entries in the Pg table
			$res_num = pg_query($PgConn, "SELECT count(*) as nb FROM ".$table);
			$PgNumEntries = pg_fetch_array($res_num, null, PGSQL_ASSOC);
			echo "*** There is now ".number_format($PgNumEntries['nb'], 0)." entries in the PostgreSQL table.\n";
			if($MyNumEntries['nb'] == $PgNumEntries['nb']) {
				// Done with this table
				echo "*** We are done with the table \"".$table."\"! How cool is that?\n";
			} else {
				// We miss some lines!
				echo "*** ...".number_format($MyNumEntries['nb'] - $PgNumEntries['nb'], 0)." were not inserted for an unknown reason.\n";
				echo "*** Please check this issue then re-launch the script.\n";
				echo "* EXITING! (sorry about that)\n";
				exit();
			}
		}
	}
}

// Closing the connexions
echo "...\n";
echo "* Closing PostgreSQL connection...\n";
mysql_close($MyConn);
echo "* Closing MySQL connection...\n";
pg_close($PgConn);
echo "* Done.\n\n";

// Function from http://stackoverflow.com/questions/6284553/using-an-array-as-needles-in-strpos
function strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(strpos(strtolower($haystack), strtolower($query), $offset) !== false) return true; // stop on first true result
    }
    return false;
}
?>
