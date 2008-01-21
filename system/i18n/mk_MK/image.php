<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'driver_not_supported'    => 'Драјверот за слики %s не постои.',

	// CI's Image_lib stuff below
	'source_image_required'   => 'Мора да се дефинира source слика во вашите преференци.',
	'gd_required'             => 'Библиотеката GD (image library) е задолжителна библиотека.',
	'gd_required_for_props'   => 'Серверот мора да ја поддржува GD image библиотеката со цел да ги прочита својствата на сликата',
	'unsupported_imagecreate' => 'Серверот не ја поддржува GD функцијата потребна за процесирање на овој тип на слика.',
	'gif_not_supported'       => 'GIF сликите обично не се поддржани поради лимитите со лиценцирањето. Можеби ќе треба да се користат JPEG или PNG слики.',
	'jpg_not_supported'       => 'JPG сликите не се поддржани',
	'png_not_supported'       => 'PNG сликите не се поддржани',
	'jpg_or_png_required'     => 'Протоколот за промена на големината на сликите назначен во вашите преференци функционира само со JPEG или PNG тип на слики.',
	'copy_error'              => 'Се појави грешка при обид да се замени оваа датотека. Проверете дали во тој директориум може да се запишува.',
	'rotate_unsupported'      => 'Ротација на слики по се изгледа не е поддржана од страна на серверот.',
	'libpath_invalid'         => 'Патеката до сликовната библиотека не е точна. Корегирајте ја патеката во преференците за сликите.',
	'image_process_failed'    => 'Процесирањето на сликата е неуспешно. Проверете дали серверот го поддржува избраниот протокол и дека патеката до сликовната библиотека е коректна.',
	'rotation_angle_required' => 'Нема агол на ротација за ротирање на сликата.',
	'writing_failed_gif'      => 'GIF слика ',
	'invalid_path'            => 'Патеката до сликата не е точна',
	'copy_failed'             => 'Копирањето на сликата е неуспешно.',
	'missing_font'            => 'Не е пронајден фонт за работа.'
);
