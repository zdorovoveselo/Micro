<?php
namespace MicroMir\Routing;

use MicroMir\{
	Exception\MicroException,
	Debug\Error\ErrorHandler
};

class RouteException extends MicroException
{
	protected $exceptionCode = 'Router';

	protected $ru = [
		1 => "Дублирование имени параметра \" {0} \" в маршруте {1}",
		2 => "Дублирование имени маршута \" {0} \"",
		3 => "Не определён Kонтроллер для маршута \" {0} \" . Метод {1} проигнорирован",
		4 => "Метод {0} проигнорирован. Причина: не определён Kонтроллер",
		5 => "Незакрытая группа \" {0} \" {1} в текущем файле",
		6 => "Лишний {0}",
		7 => "Именование {0} не выполнено. Причина: не найден route()",
		8 => "Не удалось подключить файл {0} . 	Причина: неверное имя или недостаточно прав доступа",
		9 => "Неизвестный метод {0} проигнорирован",
		10 => "Некорректный тег \" &lt;?php \" в файле {0}",
		11 => "Ошибка : \" {0} \"<br><br>
			   в подключаемом файле {1} строка {2} . Файл не подключен",
		12 => "Повторное подключение {0} . Файл не подключен",
		13 => "Повторное определение маршута {0} было отклонено",
		14 => "Повторное определение метода {0} было отклонено",
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

	public function __construct(int $num, array $m, $traceNumber = 0) {
		parent::__construct($num, $m, $traceNumber);
		// \d::p($this);
	}
}