wp-sparql
=======

A wordpress plug-in for querying data from a SPARQL endpoint and present results on WP http://wordpress.org/.

# Description

wpsparql is a wordpress plug-in that exposes a series of functionalities to bring content exposed by an sparql endpoint to Wordpress' UI.

# What is sparql?

SPARQL (pronounced "sparkle") is an RDF query language, that is, a semantic query language for databases, able to retrieve and manipulate data stored in Resource Description Framework (RDF) format. SPARQL allows for a query to consist of triple patterns, conjunctions, disjunctions, and optional patterns.

# What is a sparql endpoint?

SPARQL endpoints are services that accept SPARQL queries and return results.

# This plugin is based on wpckan

This plug-in is based on http://github.com/OpenDevelopmentMekong/wpckan/, a wordpress plug-in exposing functionality to pull and present data exposed by a CKAN instance. More about CKAN on http://ckan.org/.

# Features

## Feature 1: Query SPARQL endpoint

wpsparql exposes the shortcode **[wpsparql_query_endpoint query="QUERY"]** which can be used to generate a query and present the results returned by the endpoint.

The shortcode has following parameters:

* **query**: (Mandatory) Term to query the database.

Examples:
```php
[wpsparql_query_endpoint query="SELECT * WHERE { ?person a foaf:Person . ?person foaf:name ?name } LIMIT 1"]
```

```html
<table class="wpsparql_result_list">
  <tr>
    <th>person</th>
    <th>name</th>
  </tr>
  <tr>
    <td>some_url</td>
    <td>some_name</td>
  </tr>
</table>
```

### Widget

This feature also exposes a widget for integrating the results of queries easily into Wordpress sidebars. Just add the **WPSPARQL query endpoint** widget available under **Appearance > Widgets** to any widget area and configure the query you want to execute.

# Installation

1. Either download the files as zip or clone <code>git clone https://github.com/OpenDevelopmentMekong/wpsparql.git</code> into the Wordpress plug-ins folder.
2. Install dependencies with composer (http://getcomposer.org) <code>composer install</code>
3. Activate the plugin through the 'Plugins' menu in WordPress

# Configuration

1. Go to plug-in settings
2. Specify the URl of the sparql endpoint
3. Add the namespaces you want to support in your queries.
4. Save settings

# Development

1. Install composer http://getcomposer.org/
2. Edit composer.json for adding/modifying dependencies versions
3. Install dependencies <code>composer install</code>

# Requirements

* PHP 5 >= 5.2.0
* PHP Curl extension (in ubuntu sudo apt-get install php5-curl)

# Uses

* https://github.com/byjg/PHP-SPARQL-Lib.git

# Copyright and License

This material is copyright (c) 2014-2015 East-West Management Institute, Inc. (EWMI).

It is open and licensed under the GNU Lesser General Public License (LGPL) v3.0 whose full text may be found at:

http://www.fsf.org/licensing/licenses/lgpl-3.0.html
