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

    // URI reference vs Literal
    if (wpsparql_is_valid_url($row[$field])){
      print("<a target=\"_blank\" href=$row[$field]><i class=\"fa fa-link\" aria-hidden=\"true\"></i></a>");
    }else{
      print($row[$field]);
    }

    print"</td>";
  }
  print "</tr>";
}
print "</table>";

if (array_key_exists("more_url",$atts) && !empty($atts["more_url"])):
  $url = $atts["more_url"];
  print "<div class=\"wpsparql_more_url\">";
  print "<a target=\"_blank\" href=$url>More...</a>";
  print "</div>";
endif;

?>
