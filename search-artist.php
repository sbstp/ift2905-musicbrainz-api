<?php
include('common.php');

$query = $_GET['query'];

$res = pg_query_params($conn, "select gid as id, name, comment from artist where name ilike $1 limit 10", array("%$query%"));
$artists = pg_fetch_all($res);
if ($artists === false) {
    $artists = array();
}

$query_len = strlen($query);

// Sort by length of name cloest to query length.
usort($artists, function($a, $b) use($query_len) {
    $delta_a = abs(strlen($a['name']) - $query_len);
    $delta_b = abs(strlen($b['name']) - $query_len);
    return $delta_a - $delta_b;
});

header("Content-Type: application/json");
echo json_encode($artists);
