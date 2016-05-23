<?php if (is_null($data)) die(); ?>

<?php

print "<table class='wpsparql_result_list'>";
print "<tr>";
foreach( $data->getFields() as $field )
{
  print "<th>$field</th>";
}
print "</tr>";

foreach( $data as $row )
{
  print "<tr>";
  foreach( $data->getFields() as $field )
  {
    print "<td>";
    echo($row[$field]);
    print"</td>";
  }
  print "</tr>";
}
print "</table>";
?>
