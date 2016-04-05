<?php
include('common.php');

$sql = "
select
	t1.gid as id,
	t1.name,
	t1.length,
	t2.number,
	t4.gid as release,
	t4.name as release_name,
	t9.name as area,
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
	t9.name,
	t4.gid,
	t3.position,
	t2.position
asc nulls last
";

$res = pg_query_params($conn, $sql, array($_GET['release-group']));
$releases = array();

while (($recording = pg_fetch_object($res)) !== false) {
	if (!array_key_exists($recording->release, $releases)) {
		$releases[$recording->release] = array(
			'id' => $recording->release,
			'name' => $recording->release_name,
			'year' => $recording->year,
			'month' => $recording->month,
			'day' => $recording->day,
			'areas' => array(),
			'recordings' => array(),
		);
	}
	$release = &$releases[$recording->release];

	// add country if it isn't already there
	if (!in_array($recording->area, $release['areas'])) {
		$release['areas'][] = $recording->area;
	}

	// add recording if it isn't already there
	if (!array_key_exists($recording->id, $release['recordings'])) {
		$release['recordings'][$recording->id] = array(
			'id' => $recording->id,
			'name' => $recording->name,
			'length' => $recording->length,
		);
	}
}

$releases = array_values($releases);
foreach ($releases as &$val) {
	$val['recordings'] = array_values($val['recordings']);
}

pg_free_result($res);
pg_close($conn);

header("Content-Type: application/json");
echo json_encode($releases);
