-- http://www.gab.lc/articles/from_mysql_to_postgresql
-- At that point I had issues with PostgreSQL's serials values. A Serial is like MySQL's AUTO_INCREMENT.
-- When I tried inserting data in my tables I got sequences errors (PostgreSQL was trying to insert the "id" 1, 2, 3... in tables with already millions of lines).
-- I had to reset all the sequences' values to my last id inserted. I found a script wrote by Isura Silva, which I slightly modified to avoid a few issues.
-- It is written in PL/pgSQL. Here is the script you can easily copy/paste in psql:
-- (if you want, you can find the original version here: http://isura777.blogspot.com/2012/06/reset-all-sequences-current-values.html)
-- You can then call it with psql by running the following command:
--    SELECT * FROM seq_update();
-- It worked. It worked great. 

CREATE OR REPLACE FUNCTION seq_update()
  RETURNS void AS
$BODY$
Declare
 tab1 varchar;
 col1 varchar;
 seqname1 varchar;
 maxcolval integer;
 ssql varchar;
BEGIN
FOR tab1, col1, seqname1 in Select distinct constraint_column_usage.table_name,
 constraint_column_usage.column_name,
 replace(replace(columns.column_default,'''::regclass)',''),'nextval(''','')
 From information_schema.constraint_column_usage, information_schema.columns
 where constraint_column_usage.table_schema ='public' AND
    columns.table_schema = 'public'
    AND columns.table_name=constraint_column_usage.table_name
    AND constraint_column_usage.column_name = columns.column_name
    AND columns.column_default is not null
    AND constraint_column_usage.table_name not in ('user', 'usermodulespages')
    --AND constraint_column_usage.table_name = 'role'
 order by 1
LOOP
 ssql := 'select max(' || col1 || ') + 1 as max from ' || tab1 ;
 RAISE NOTICE 'SQL : %', ssql;
 execute ssql into maxcolval;
 RAISE NOTICE 'max value : %', maxcolval;
 EXECUTE 'alter sequence ' || seqname1 ||' restart  with ' || maxcolval;
END LOOP;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
