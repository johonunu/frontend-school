<?php

require 'vendor/autoload.php';

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);

$app = new \Slim\Slim();

// Dodaj JSON midleware globalno
$app->add(new \SlimJson\Middleware(array(
  'json.status' => true,
  'json.override_error' => true,
  'json.override_notfound' => true,
  'json.status' => false,
)));

// DB CONNECT
$pdo = new PDO("sqlite:db.sqlite");
$fpdo = new FluentPDO($pdo);

// GET /persons
$app->get('/persons', function() use ($app, $fpdo) {

	$count = $fpdo->from('persons')->count();

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
		$persons = $fpdo->from('persons')->limit($perPage)->offset(($page-1)*$perPage)->fetchAll();
	}else{
		$page = 1;
		$persons = $fpdo->from('persons')->limit($perPage)->fetchAll();
	}

	$meta = [
  		'pageCount'=>$pageCount,
  		'currentPage' => $page,
  		'totalCount'=>$count,
  		'next'=>($page==$pageCount?false:true),
  		'previous'=>($page==1?false:true)
	];

    $app->response->headers->set('Pagination-Page-Count', $pageCount);
    $app->response->headers->set('Pagination-Current-Page', $page);
    $app->response->headers->set('Pagination-Total-Count', $count);
    $app->response->headers->set('Pagination-Next', ($page==$pageCount?'false':'true'));
    $app->response->headers->set('Pagination-Previous', ($page==1?'false':'true'));

  	$app->render(200, $persons);
});

// GET /persons/:id
$app->get('/persons/:id', function($id) use ($app, $fpdo) {
  $person = $fpdo->from('persons')->where('id', $id)->fetch();
  if(empty($person)){
    throw new \Exception('Person with id '.$id.' does not exist.');
  }
  $app->render(200, $person);
});

// POST /persons
$app->post('/persons', function() use ($app, $fpdo) {
  $data = $app->request->post();
  if(empty($data)){
    $data = json_decode($app->request->getBody(),true);
  }
  $savedId = $fpdo->insertInto('persons', $data)->execute();
  if(!$savedId){
    throw new \Exception('Saving person failed.');
  }
  $app->render(200, ['saved' => true, 'id' => $savedId]);
});

// PUT /persons/:id
$app->put('/persons/:id', function($id) use ($app, $fpdo) {
  $data = $app->request->post();
  if(empty($data)){
    $data = json_decode($app->request->getBody(),true);
  }
  $updated = $fpdo->update('persons', $data,$id)->execute();
  if(!$updated){
    throw new \Exception('Person with id '.$id.' does not exist.');
  }
  $app->render(200, ['updated' => true, 'id' => $id]);
});

// DELETE /persons/:id
$app->delete('/persons/:id', function($id) use ($app, $fpdo) {
  $deleted = $fpdo->deleteFrom('persons', $id)->execute();
  if(!($deleted)){
    throw new \Exception('Person with id '.$id.' does not exist.');
  }
  $app->render(200, ['deleted' => $deleted]);
});

$app->run();
