<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'ini.php';

$putanja = explode('/', $_SERVER['REQUEST_URI']);

array_shift($putanja);
array_shift($putanja);
array_pop($putanja);

$file = DIR . implode(DS, $putanja) . DS . $_REQUEST['file'];

if ($app->getContainer()->auth->isLoggedIn())
{
	$contentType = mime_content_type($file);
    header("Content-type: $contentType");
    header('Content-Disposition: inline;');
    header('Cache-Control: no-cache');
    readfile($file);
}
else
{
	header("HTTP/1.1 403 Forbidden");
}

/*
	U direktorijum koji treba zastititi dodati .htaccess fajl sa sadrzajem:

	RewriteEngine on  
	RewriteRule ^(.*)$ ../../app/prx.php?file=$1
*/
