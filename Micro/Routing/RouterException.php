<?php
namespace MicroMir\Routing;

use MicroMir\{
	Exception\MicroException,
	Debug\Error\ErrorHandler
};

class RouterException extends MicroException
{
	protected $exceptionCode = 'Router';

	protected $ru = [
		1 => "Дублирование имени параметра \"{0}\" в маршруте {1}",
		2 => "Дублирование имени маршрута \"{0}\"",
		3 => "Не определён Kонтроллер для маршрута \"{0}\" . Метод {1} проигнорирован",
		4 => "Метод {0} проигнорирован. Причина: не определён Kонтроллер",
		5 => "Незакрытая группа {0} в текущем файле",
		6 => "Лишний {0}",
		7 => "Именование {0} не выполнено. Причина: не найден route()",
		8 => "Не удалось подключить файл {0} . Причина: неверное имя или недостаточно прав доступа",
		9 => "Неизвестный метод {0} проигнорирован",
		10 => "Некорректный тег \"&lt;?php\" в файле {0}",
		11 => "Ошибка : \"{0}\" в подключаемом файле {1} строка {2} . Файл не подключен",
		12 => "Повторное подключение {0} . Файл не подключен",
//		13 => "Повторное определение маршута {0} было отклонено",
		14 => "Повторное определение метода {0} было отклонено",
		15 => "{0} проигнорирован . Так как должен следовать за {1}",
//		16 => "Метод {0} вызван без параметров. Метод проигнорирован",
		17 => "Маршрут {0} перекрывает маршрут {1} в методе {2}",
		18 => "Метод {0} не работает для маршрутов с параметрами",
		19 => "Метод {0} работает только для маршрутов с параметрами",
		20 => "Неверное имя параметра regex(['{0} => ...']) . Параметр проигнорирован",
		21 => "Переданный параметр не являетя массивом в {0}",
		22 => "Повторное именование маршрута {0}",
		23 => "{0}",
		24 => "Закройте предыдущую группу controller('{0}')",
		25 => "Нельзя подключать файлы внутри группы контроллера.
				Текущая группа controller('{0}'). Файл не подключен",
		26 => "Незакрытая группа controller('{0}') в файле {1}",
	];

	protected $en = [
		1 => "Duplicate parameter name \" {0} \" in a route {1}",
		2 => "Duplicate route name \" {0} \"",
		3 => "Not defined Controller for the route \" {0} \" . The method {0} is ignored",
		4 => "The method {0} is ignored. Reason: the Controller isn't defined",
		5 => "Not closed group \" {0} \" {1} in the list of routes",
		6 => "Excess {0}",
		7 => "Method {0} ignored. Reason: not found route()",
		8 => "Unable to include file {0}",
	];

	public function __construct(int $num, array $replace, $traceNumber = 0) {
		parent::__construct($num, $replace, $traceNumber);
		// \d::p($this);
	}
}