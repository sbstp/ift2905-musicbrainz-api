<?php
include('common.php');

$sql = "
select
	t4.gid as id,
	t4.name as name,
	coalesce(t5.date_year, t6.date_year) as year,
	coalesce(t5.date_month, t6.date_month) as month,
	coalesce(t5.date_day, t6.date_day) as day,
	t7.name as primary_type,
	(select array_to_string(array(
		select name from release_group_secondary_type_join t8
		inner join release_group_secondary_type t9 on t9.id = t8.secondary_type
		where t8.release_group = t4.id and t8.secondary_type in (1,2,6,7) -- compilation, live, remix, soundtrack
	), ',')) as secondary_types
from release t1
	inner join artist_credit_name t2 on t1.artist_credit = t2.artist_credit
	inner join artist t3 on t2.artist = t3.id
	inner join release_group t4 on t1.release_group = t4.id
	left outer join release_country t5 on t1.id = t5.release
	left outer join release_unknown_country t6 on t1.id = t6.release
	inner join release_group_primary_type t7 on t4.type = t7.id
where
	t3.gid = $1 and
	t1.status = 1 and -- official
	t4.type in (1, 2, 3) -- album, single, ep
order by year, month, day asc nulls last
";

$res = pg_query_params($conn, $sql, array($_GET['artist']));
$releaseGroups = array();

while (($entry = pg_fetch_object($res)) !== false) {
	if (!array_key_exists($entry->id, $releaseGroups)) {
		if (empty($entry->secondary_types)) {
			$entry->secondary_types = array();
		} else {
			$entry->secondary_types = explode(',', $entry->secondary_types);
		}
		$releaseGroups[$entry->id] = $entry;
	}
}

$releaseGroups = array_values($releaseGroups);

header("Content-Type: application/json");
echo json_encode($releaseGroups);
