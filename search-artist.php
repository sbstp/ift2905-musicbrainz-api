<?php
include('common.php');

$query = $_GET['query'];

$sql = "
select
    gid as id,
    name,
    comment
from artist
where
    name ilike $1
order by char_length(name) asc
limit 25
";

$res = pg_query_params($conn, $sql, array("%$query%"));
$artists = pg_fetch_all($res);
if ($artists === false) {
    $artists = array();
}

header("Content-Type: application/json");
echo json_encode($artists);
