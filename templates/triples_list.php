<?php if (is_null($data)) die(); ?>

<?php
print_r($data);
print "<table class='example_table'>";
print "<tr>";
foreach( $data as $field )
{
  print "<th>$field</th>";
}

?>
