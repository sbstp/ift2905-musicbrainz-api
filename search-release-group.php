<?php
include('common.php');

$query = $_GET['query'];

$sql = "
select * from (
	select distinct on(t1.gid)
		t1.gid as id,
		t1.name,
		array_to_json(array(
			select json_build_object('id', t3.gid, 'name', t3.name, 'comment', t3.comment)
			from artist t3
			inner join artist_credit_name t4 on t4.artist = t3.id
			inner join artist_credit t5 on t5.id = t4.artist_credit and t5.id = t1.artist_credit
		)) as credits
	from release_group t1
		inner join release t2 on t2.release_group = t1.id
	where
		t2.status = 1 and -- official
		t1.type in (1,2,3) and -- album, single, ep
		t1.name ilike $1
) as sub1 order by char_length(name) asc limit 25
";

$res = pg_query_params($conn, $sql, array("%$query%"));
$releaseGroups = pg_fetch_all($res);
if ($releaseGroups === false) {
    $releaseGroups = array();
}

foreach ($releaseGroups as &$releaseGroup) {
	$releaseGroup['credits'] = json_decode($releaseGroup['credits']);
}

header("Content-Type: application/json");
echo json_encode($releaseGroups);
