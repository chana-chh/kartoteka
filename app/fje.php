<?php

function getStringBetween($string, $start, $end)
{
	$string = " " . $string;
	$ini = strpos($string, $start);
	if ($ini == 0)
		return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function latinicaUCirilicu(string $tekst, bool $cirilica_u_latinicu = false)
{
	$latinica = [
		'Đ',
		'Dj',
		'DJ',
		'Lj',
		'LJ',
		'Nj',
		'NJ',
		'Dž',
		'DŽ',
		'A',
		'B',
		'V',
		'G',
		'D',
		'E',
		'Ž',
		'Z',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'R',
		'S',
		'T',
		'Ć',
		'U',
		'F',
		'H',
		'C',
		'Č',
		'Š',
		'đ',
		'dj',
		'lj',
		'nj',
		'dž',
		'a',
		'b',
		'v',
		'g',
		'd',
		'e',
		'ž',
		'z',
		'i',
		'j',
		'k',
		'l',
		'm',
		'n',
		'o',
		'p',
		'r',
		's',
		't',
		'ć',
		'u',
		'f',
		'h',
		'c',
		'č',
		'š',
	];
	$cirilica = [
		'Ђ',
		'Ђ',
		'Ђ',
		'Љ',
		'Љ',
		'Њ',
		'Њ',
		'Џ',
		'Џ',
		'А',
		'Б',
		'В',
		'Г',
		'Д',
		'Е',
		'Ж',
		'З',
		'И',
		'Ј',
		'К',
		'Л',
		'М',
		'Н',
		'О',
		'П',
		'Р',
		'С',
		'Т',
		'Ћ',
		'У',
		'Ф',
		'Х',
		'Ц',
		'Ч',
		'Ш',
		'ђ',
		'ђ',
		'љ',
		'њ',
		'џ',
		'а',
		'б',
		'в',
		'г',
		'д',
		'е',
		'ж',
		'з',
		'и',
		'ј',
		'к',
		'л',
		'м',
		'н',
		'о',
		'п',
		'р',
		'с',
		'т',
		'ћ',
		'у',
		'ф',
		'х',
		'ц',
		'ч',
		'ш',
	];
	if ($cirilica_u_latinicu)
	{
		return str_replace($cirilica, $latinica, $tekst);
	}
	else
	{
		return str_replace($latinica, $cirilica, $tekst);
	}
}

function isValidJMBG(string $jmbg)
{
	$len = strlen($jmbg);
	if ($len != 13)
	{
		return false;
	}
	$niz = str_split($jmbg);
	$ok = true;
	$zbir = 0;
	foreach ($niz as $k => $v)
	{
		if (!is_numeric($v))
		{
			return false;
		}
		$niz[$k] = (int)$v;
	}
	$zbir = $niz[0] * 7
		+ $niz[1] * 6
		+ $niz[2] * 5
		+ $niz[3] * 4
		+ $niz[4] * 3
		+ $niz[5] * 2
		+ $niz[6] * 7
		+ $niz[7] * 6
		+ $niz[8] * 5
		+ $niz[9] * 4
		+ $niz[10] * 3
		+ $niz[11] * 2;
	$ostatak = $zbir % 11;
	if ($ostatak === 1)
	{
		return false;
	}
	$kontrolni = 11 - $ostatak;
	if ($ostatak == 0)
	{
		$kontrolni = 0;
	}
	if ($kontrolni != $niz[12])
	{
		return false;
	}
	return true;
}

function error($description, $param = null)
{
	echo '<h1 style="font-family: sans-serif; color: red">GREŠKA</h1>';
	echo '<p style="font-family: sans-serif; font-size: 18px;">' . $description . '</p>';
	if ($param)
	{
		echo '<p style="font-family: monospace; font-size: 18px;"><code>[' . $param . ']</code></p>';
	}
	die();
}

function dd($var, $print = true, $die = true, $backtrace = false)
{
	if (gettype($var) === 'object')
	{
		echo '<h2 style="font-family:monospace;color:#900;padding:1rem;"><em>Object:</em> ' . get_class($var) . '</h2>';
	}
	else
	{
		echo '<h2 style="font-family:monospace;color:#900;padding:1rem;"><em>Variable:</em> ' . gettype($var) . '</h2>';
	}

	echo '<pre style="background-color:#fdd;color:#000;padding:1rem;">';
	if ($print)
	{
		print_r($var);
	}
	else
	{
		var_dump($var);
	}
	echo '</pre>';

	if (gettype($var) === 'object')
	{
		echo '<h2 style="font-family:monospace;color:#090;padding:1rem;"><em>Object:</em> methods</h2>';
		echo '<pre style="background-color:#dfd;color:#000;padding:1rem;">';
		print_r(get_class_methods($var));
		echo '</pre>';
	}

	if ($backtrace)
	{
		echo '<h2 style="font-family:monospace;color:#009;padding:1rem;">Backtrace</h2>';
		echo '<pre style="background-color:#ddf; color:#000;padding:1rem;">';
		print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		echo '</pre>';
	}

	if ($die)
	{
		die();
	}
}

function iznosSlovima($broj)
{
	$br = number_format($broj, 2, ',', '');
	if (strpos($br, ","))
	{
		list($dinara, $para) = explode(",", $br);
	}
	else
	{
		$dinara = $broj;
		$para = 0;
	}
	$din = "динар";
	if (substr($dinara, -1) !== "1")
	{
		$din .= "а";
	}

	$f = new \NumberFormatter('sr', NumberFormatter::SPELLOUT);
	$rez = $f->format($dinara);
	$rez = str_replace(' и ', '', $rez);
	$rez = str_replace(' ', '', $rez);
	dd($rez . ' ' . $din . ' и ' . $para . '/100');
}
