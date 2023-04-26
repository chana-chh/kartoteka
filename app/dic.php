<?php

$container = $app->getContainer();

// CSRF protection instance - csrf
$container['csrf'] = function ($container)
{
    return new \Slim\Csrf\Guard;
};

// Validation instance - validator
$container['validator'] = function ($container)
{
    return new \App\Classes\Validator();
};

// Authorization instance - auth
$container['auth'] = function ($container)
{
    return new \App\Classes\Auth(new \App\Models\Korisnik());
};

$container['logger'] = function ($container)
{
    return new \App\Classes\Logger($container->auth->user());
};

// Flash messages instance - flash
$container['flash'] = function ()
{
    return new \Slim\Flash\Messages;
};

// Twig view instance - view
$container['view'] = function ($container)
{
    $conf = $container['settings']['renderer'];
    $view = new \Slim\Views\Twig($conf['template_path'], ['cache' => $conf['cache_path'], 'debug' => true]);
    $router = $container->router;
    $uri = $container->request->getUri();
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->getEnvironment()->addGlobal('auth', [
        'logged' => $container->auth->isLoggedIn(),
        'user' => $container->auth->user(),
    ]);
    $view->getEnvironment()->addGlobal('URL', URL);
    $view->getEnvironment()->addGlobal('DIR', DIR);
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(new Slim\Flash\Messages));
    $view->addExtension(new Twig_Extension_Debug);
	$function = new \Twig\TwigFunction('latinicaUCirilicu',function(string $tekst){
		$latinica = [
			'Đ', 'Dj', 'DJ', 'Lj', 'LJ', 'Nj', 'NJ', 'Dž', 'DŽ',
			'A', 'B', 'V', 'G', 'D', 'E', 'Ž', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'Ć', 'U', 'F', 'H', 'C', 'Č', 'Š',
			'đ', 'dj', 'lj', 'nj', 'dž',
			'a', 'b', 'v', 'g', 'd', 'e', 'ž', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'ć', 'u', 'f', 'h', 'c', 'č', 'š',
		];
		$cirilica = [
			'Ђ', 'Ђ', 'Ђ', 'Љ', 'Љ', 'Њ', 'Њ', 'Џ', 'Џ',
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш',
			'ђ', 'ђ', 'љ', 'њ', 'џ',
			'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'ш',
		];
		return str_replace($latinica, $cirilica, $tekst);
	});
	$view->getEnvironment()->addFunction($function);
    return $view;
};
