<?php

require 'vendor/autoload.php';

// Koristi :
// - SlimPHP - http://www.slimframework.com (kao minimalni frejmork)
// - FluentPDO - http://lichtner.github.io/fluentpdo (za rad sa bazom podataka)

// Ukljuci debug
// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);


$app = new \Slim\Slim();

// Dodaj JSON midleware globalno
$app->add(new \SlimJson\Middleware(array(
  'json.status' => true,
  'json.override_error' => true,
  'json.override_notfound' => true
)));

// Povezi se sa bazom
$pdo = new PDO("sqlite:db.sqlite");
$fpdo = new FluentPDO($pdo);

// API za dohvatanje podataka o umetniku, njegovim albumima i pesmama
$app->get('/artists/:id', function($id) use ($app, $fpdo) {

	$artist = $fpdo->from('artist')->where('ArtistId', $id)->fetch();

	if(empty($artist)){
		throw new \Exception('Artist with id '.$id.' does not exist.');
	}
	
	$artist['albums'] = $fpdo->from('album')->where('ArtistId', $id)->fetchAll();

	foreach ($artist['albums'] as $i=>$album) {
		$artist['albums'][$i]['Tracks'] = $fpdo->from('track')->where('AlbumId', $album['AlbumId'])->fetchAll();
	}

	$app->render(200, ['data' => $artist]);
});

// API za listanje umetinka sa paginacijom
$app->get('/artists', function() use ($app, $fpdo) {

	$count = $fpdo->from('artist')->count();
	
	if(isset($_GET['perPage']) && intval($_GET['perPage']) > 0){
		$perPage = intval($_GET['perPage']);
	}else{
		$perPage = 5;
	}

	$pageCount = ceil($count/$perPage);

	if(isset($_GET['page']) && intval($_GET['page']) > 0){
		$page = intval($_GET['page']);
		if($page > $pageCount){
			throw new \Exception('Page '.$page.' does not exist.');
		}
		$artists = $fpdo->from('artist')->limit($perPage)->offset(($page-1)*$perPage)->fetchAll();
	}else{
		$page = 1;
		$artists = $fpdo->from('artist')->limit($perPage)->fetchAll();
	}

	$meta = [
  		'pageCount'=>$pageCount,
  		'currentPage' => $page,
  		'totalCount'=>$count,
  		'next'=>($page==$pageCount?false:true),
  		'previous'=>($page==1?false:true)
	];

  	$app->render(200, ['data' => $artists,'_meta'=>$meta]);
});

$app->run();
