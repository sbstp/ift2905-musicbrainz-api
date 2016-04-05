<?php
include('common.php');

$query = $_GET['query'];

$sql = "
select
	t1.gid as id,
	t1.name,
	t2.name artist
from release_group t1
    inner join artist_credit t2 on t2.id = t1.artist_credit
where
    t1.name ilike $1
order by char_length(t1.name) asc
limit 25
";

$res = pg_query_params($conn, $sql, array("%$query%"));
$artists = pg_fetch_all($res);
if ($artists === false) {
    $artists = array();
}

header("Content-Type: application/json");
echo json_encode($artists);
