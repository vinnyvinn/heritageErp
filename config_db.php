<?php

/*Connection Information for the database
$def_coy - the default company that is pre-selected on login

'host' - the computer ip address or name where the database is. The default is 'localhost' assuming that the web server is also the sql server.

'port' - the computer port where the database is. The default is '3306'. Set empty for default.

'dbuser' - the user name under which the company database should be accessed.
  NB it is not secure to use root as the dbuser with no password - a user with appropriate privileges must be set up.

'dbpassword' - the password required for the dbuser to authorise the above database user.

'dbname' - the name of the database as defined in the RDMS being used. Typically RDMS allow many databases to be maintained under the same server.
'collation' - the character set used for the database.
'tbpref' - prefix on table names, or '' if not used. Always use non-empty prefixes if multiply company use the same database.
*/


$def_coy = 1;

$tb_pref_counter = 2;

$db_connections = array (
  0 => 
  array (
    'name' => 'Wizag',
    'host' => 'localhost',
    'port' => '3306',
    'dbname' => 'cool',
    'collation' => 'utf8_xx',
    'tbpref' => '0_',
    'dbuser' => 'root',
    'dbpassword' => '12345678',
  ),
  1 => 
  array (
    'name' => 'HERITAGE',
    'host' => 'localhost',
    'port' => '3306',
    'dbuser' => 'root',
    'dbpassword' => '12345678',
    'dbname' => 'heritage',
    'collation' => 'utf8_xx',
    'tbpref' => '1_',
  ),
);
