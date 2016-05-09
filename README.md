wp-sparql
=======

# DISCLAIMER: WIP!!!! Not production ready yet!!!!!!

A wordpress plugin for querying data from an SPARQL endpoint into WP http://wordpress.org/.

# Description

wpsparql is a wordpress plugin that exposes a series of functionalities to bring content exposed by an sparql endpoint to Wordpress' UI.

# What is sparql?

SPARQL (pronounced "sparkle") is an RDF query language, that is, a semantic query language for databases, able to retrieve and manipulate data stored in Resource Description Framework (RDF) format. SPARQL allows for a query to consist of triple patterns, conjunctions, disjunctions, and optional patterns.

# What is a sparql endpoint?

SPARQL endpoints are services that accept SPARQL queries and return results.

# This plugin is based on wpckan

This plugin is based on http://github.com/OpenDevelopmentMekong/wpckan/, a wordpress plugin exposing functionality to pull and present data exposed by a CKAN instance. More about CKAN on http://ckan.org/.

# Features

## Feature 1: Add related SPARQL datasets to posts.

TBD

## Feature 2: Query SPARQL endpoint

TBD

# Installation

1. Either download the files as zip or clone recursively (contains submodules) <code>git clone https://github.com/OpenDevelopmentMekong/wpsparql.git --recursive</code> into the Wordpress plugins folder.
2. Activate the plugin through the 'Plugins' menu in WordPress

# Development

1. Install composer http://getcomposer.org/
2. Edit composer.json for adding/modifying dependencies versions
3. Install dependencies <code>composer install</code>

# Requirements

* PHP 5 >= 5.2.0
* PHP Curl extension (in ubuntu sudo apt-get install php5-curl)

# Uses

*

# Copyright and License

This material is copyright (c) 2014-2015 East-West Management Institute, Inc. (EWMI).

It is open and licensed under the GNU Lesser General Public License (LGPL) v3.0 whose full text may be found at:

http://www.fsf.org/licensing/licenses/lgpl-3.0.html
