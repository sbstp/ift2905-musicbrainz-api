<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = pg_connect("host=/var/run/postgresql user=musicbrainz dbname=musicbrainz_db");
