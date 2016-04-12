<?php
include('common.php');

$query = $_GET['query'];

$sql = "
select
    t1.gid as id,
    t1.name,
    t1.comment,
    (select url from artist t2
    	inner join l_artist_url t3 on t3.entity0 = t2.id
    	inner join url t4 on t4.id = t3.entity1
    	where t2.id = t1.id and url like '%commons.wikimedia.org%'
    	limit 1) as image
from artist t1
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

// convert wikimedia file info url to actual file url
function get_image_from_url($url) {
    if (!preg_match('/File:(.+)$/', $url, $matches)) {
        return null;
    }
    $file = $matches[1];
    $hash = md5($file);
    $a = $hash[0];
    $b = $hash[1];
    $img = "http://upload.wikimedia.org/wikipedia/commons/$a/$a$b/$file";
    return $img;
}

foreach ($artists as &$artist) {
    $artist['image'] = get_image_from_url($artist['image']);
}

header("Content-Type: application/json");
echo json_encode($artists);
