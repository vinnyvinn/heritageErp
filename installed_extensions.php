<?php

/* List of installed additional extensions. If extensions are added to the list manually
	make sure they have unique and so far never used extension_ids as a keys,
	and $next_extension_id is also updated. More about format of this file yo will find in 
	FA extension system documentation.
*/

$next_extension_id = 2; // unique id for next installed extension

$installed_extensions = array (
  1 => 
  array (
    'name' => 'Theme Bluecollar',
    'package' => 'bluecollar',
    'version' => '2.4.0-3',
    'type' => 'theme',
    'active' => false,
    'path' => 'themes/bluecollar',
  ),
);
