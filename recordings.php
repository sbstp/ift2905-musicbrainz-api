<?php
include('common.php');

$album = $_GET['album'];

$sql = "
select
	t1.gid as id,
	t1.name,
	t1.length,
	t4.gid as release,
	t4.name as release_name,
	t9.name as country,
	coalesce(t6.date_year, t7.date_year) as year,
	coalesce(t6.date_month, t7.date_month) as month,
	coalesce(t6.date_day, t7.date_day) as day
from recording t1
	inner join track t2 on t2.recording = t1.id
	inner join medium t3 on t3.id = t2.medium
	inner join release t4 on t4.id = t3.release
	inner join release_group t5 on t5.id = t4.release_group
	left outer join release_country t6 on t6.release = t4.id
	left outer join release_unknown_country t7 on t7.release = t4.id
	left outer join country_area t8 on t8.area = t6.country
	left outer join area t9 on t9.id = t8.area
where t5.gid = $1
order by
	coalesce(t6.date_year, t7.date_year),
	coalesce(t6.date_month, t7.date_month),
	coalesce(t6.date_day, t7.date_day),
	t4.gid,
	t2.number
asc nulls last
";

$res = pg_query_params($conn, $sql, array($album));
$recordings = pg_fetch_all($res);

function int_or_null($val) {
	if ($val == null) {
		return null;
	} else {
		return (int) $val;
	}
}

$releases_tmp = array();
$extras = array();

foreach ($recordings as $recording) {
	$release = $recording['release'];
	if (!array_key_exists($release, $releases_tmp)) {
		$releases_tmp[$release] = array();
	}
	if (!array_key_exists($release, $extras)) {
		$extras[$release] = array(
			"name" => $recording['release_name'],
			"country" => $recording['country']
		);
	}
	$releases_tmp[$release][] = array(
		"id" => $recording['id'],
		"name" => $recording['name'],
		"length" => int_or_null($recording['length']),
		"year" => int_or_null($recording['year']),
		"month" => int_or_null($recording['month']),
		"day" => int_or_null($recording['day'])
	);
}

$releases = array();
foreach ($releases_tmp as $key => $val) {
	$releases[] = array(
		"id" => $key,
		"name" => $extras[$key]['name'],
		"country" => $extras[$key]['country'],
		"recordings" => $val
	);
}

header("Content-Type: application/json");
echo json_encode($releases);
