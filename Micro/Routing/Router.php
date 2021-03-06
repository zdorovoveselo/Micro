<?php
namespace MicroMir\Routing;

class Router
{
    private $methods = [];

    private $routes = [];

    private $simple = [];

    private $regex = [];

    private $name = [];

    private $urlNodes = [];

    private $nameSpace = [];

    private $controllerGroup;

    private $controllerSpace = [];

    private $safeMode;

    private $routeFiles = [];

    private $match404 = [];

    private $last = false;

    /**
     * @param array $verbs HTTP методы (глаголы)
     * @param array $routePaths пути файлов с маршрутами
     * @param string $safe режим обработки файлов с маршрутами
     */
    public function __construct(array $verbs, array $routePaths, $safe = '')
    {
        $this->methods = $verbs;

        $safe == 'notSafe' ? $this->safeMode = 0 : $this->safeMode = 1;

        $this->match404['code'] = 404;
        $this->match404['nSpace'] = '';

        foreach ($routePaths as $path) {
            $this->inclusion($path);
        }
        if ($this->safeMode) {
            $this->checkRegex();
        }
    }

    private function includeFile($path)
    {
        $this->inclusion($path);
        return $this;
    }

    private function inclusion($path)
    {
        if ($this->safeMode) {
            if (!is_readable($path)) {
                new RouterException(8, [$path], 1);
                return;

            } elseif (mime_content_type($path) != 'text/x-php') {
                new RouterException(10, [$path], 1);
                return;
            }
        }
        $Router = $this;

        if ($this->safeMode) {

            if (array_key_exists($path, $this->routeFiles)) {
                new RouterException(12, [$path]);
                return;
            }
//            if (!empty($this->controllerGroup)) {
//                new RouterException(25, [$this->controllerGroup], 1);
//                return;
//            }
        }
        $this->routeFiles[$path] = $this->safeMode;

        ++$this->safeMode;

        $this->urlNodes[$path] = [];
        end($this->urlNodes);

        $this->nameSpace[$path] = [];
        end($this->nameSpace);

        $this->controllerSpace[] = '';

        $this->last = false;

        try {
            include $path;

        } catch (\Error $e) {
            new RouterException(11, [$e->getMessage(), $e->getFile(), $e->getLine()]);

            --$this->safeMode;

            array_pop($this->routeFiles);

            unset($this->urlNodes[$path]);
            end($this->urlNodes);

            unset($this->nameSpace[$path]);
            end($this->nameSpace);

            array_pop($this->controllerSpace);

            return $this;
        }

        $this->checkGroup($this->urlNodes, 'node');
        $this->checkGroup($this->nameSpace, 'nameSpace');
//        if ($this->controllerGroup) {
//            new RouterException(26, [$this->controllerGroup, $path], 1);
//        }

        $file = key($this->urlNodes);
        if (empty($this->urlNodes[$file])) {
            $this->urlNodes[$file] = '';
            unset($this->urlNodes[$file]);
        }

        $file = key($this->nameSpace);
        if (empty($this->nameSpace[$file])) {
            $this->nameSpace[$file] = '';
            unset($this->nameSpace[$file]);
        }

        array_pop($this->controllerSpace);
        $this->controllerGroup = null;
        return;
    }

    private function notSafe()
    {
        --$this->safeMode;
        return $this;
    }

    private function node($route = '')
    {
        $file = key($this->urlNodes);
        $this->urlNodes[$file][] = $route;
        end($this->urlNodes[$file]);

        $this->last = 'node';

        return $this;
    }

    private function nodeEnd()
    {
        $file = key($this->urlNodes);

        if (empty($this->urlNodes) || null === array_pop($this->urlNodes[$file])) {
            new RouterException(6, ['nodeEnd()']);
        }
        $this->last = 'nodeEnd';

        return $this;
    }

    private function nameSpace($space = '')
    {
        $file = key($this->nameSpace);
        $this->nameSpace[$file][] = $space;
        end($this->nameSpace[$file]);

        $this->last = 'nameSpace';

        return $this;
    }

    private function End_nameSpace()
    {
        $file = key($this->nameSpace);

        if (empty($this->nameSpace) || null === array_pop($this->nameSpace[$file])) {
            new RouterException(6, ['End_nameSpace()']);
        }
        $this->last = 'End_nameSpace';

        return $this;
    }

    private function controller($controller = null)
    {
        if ($this->safeMode) {

            if (!$controller) {
                new RouterException(16, [__FUNCTION__.'()']);
                return $this;
            }
//            if (!empty($this->controllerGroup)) {
//                new RouterException(24, [$this->controllerGroup]);
//                return $this;
//            }
        }
        $this->controllerGroup = $controller;

        return $this;
    }

//    private function End_controller($value = null) {
//        if ($this->controllerGroup === null) {
//            new RouterException(6, ["End_controller('$value')"]);
//        }
//        $this->controllerGroup = null;
//
//        return $this;
//    }

    private function controllerSpace($space = null)
    {
        if ($this->safeMode) {
            if ($this->last) {
                new RouterException(15, ['controllerSpace()', '$Router']);
                return $this;
            }
            if (!$space) {
                new RouterException(16, [__FUNCTION__.'()']);
                return $this;
            }
        }
        $this->controllerSpace[count($this->controllerSpace) - 1] = $space.'\\';

        return $this;
    }

    private function route($route, $controller = null)
    {
//        if ($this->safeMode && !$route) {
//            new RouterException(16, [__FUNCTION__.'()']);
//            return $this;
//        }
        $nodes[] = '';
        foreach ($this->urlNodes as $File) {
            foreach ($File as $Node) {
                $nodes[] = $Node;
            }
        }
        $arr['route'] = implode('', $nodes);
        $arr['route'] .= $route;

        $arr['route'] == '/' ?: $arr['route'] = rtrim($arr['route'], '/');

        // проверка на уникальность роута - переделать с методом!
//        if ($this->safeMode) {
//            if (array_key_exists($arr['route'], $this->routes)) {
//                new RouterException(13, [$arr['route']]);
//                return $this;
//            }
//        }

        //проверка и добавление контроллера
        if ($controller) {
            $arr['controller'] = end($this->controllerSpace).$controller;
        } elseif ($this->controllerGroup) {
            $arr['controller'] = end($this->controllerSpace).$this->controllerGroup;
        } else {
            new RouterException(3, [$route, 'route()']);
            return $this;
        }

        if (!strpos($arr['route'], '{')) {
            $arr['type'] = 'simple';
        } else {
            $arr['type'] = 'regex';
            $parts = preg_split('#/#', $arr['route'], -1, PREG_SPLIT_NO_EMPTY);
            $arr['mask'] = '#^';
            $optional = 0;

            for ($i = 0; $i < count($parts); ++$i) {
                if (preg_match('/^{.+}$/', $parts[$i])) {

                    $param = trim($parts[$i], '{}');

                    $param = preg_replace('/\?/', '', $param, 1, $opt);

                    if ($opt) {
                        if (++$optional == 1) {
                            $arr['optional'] = $i;
                        }
                    }
                    if (!$optional) {
                        $arr['mask'] .= '/.+';
                    }
                    $arr['parts'][$i] = '.+';

                    if (isset($arr['params'][$param])) {
                        new RouterException(1, [$param, $route]);
                    }
                    $arr['params'][$param] = $i;
                } else {
                    $arr['mask'] .= '/'.$parts[$i];
                    $arr['parts'][$i] = $parts[$i];
                }
            }
            $optional
                ?
                $arr['mask'] .= '(/(.*))?$#'
                :
                $arr['mask'] .= '$#';
        }
        if ($this->safeMode) {
            $arr['file'] = debug_backtrace()[0]['file'].'::'.debug_backtrace()[0]['line'];
        }
        $this->routes[$arr['route']] = $arr;
        end($this->routes);
        $this->last = 'route';

//        return $this;
    }

    private function regex($regexArr = null)
    {
        if (!is_array($regexArr)) {
            new RouterException(21, [__FUNCTION__."( $regexArr )"]);
            return $this;
        }
        if ($this->last != 'route') {
            new RouterException(15, ['regex()', 'route()']);
            return $this;
        }
        $route = &$this->routes[key($this->routes)];
        if (!array_key_exists('mask', $route)) {
            new RouterException(19, ['regex()']);
            return $this;
        }
        foreach ($regexArr as $key => $value) {
            if (array_key_exists($key, $route['params'])) {
                $route['parts'][$route['params'][$key]] = $value;
            } else {
                new RouterException(20, [$key]);
            }
        }
        if (array_key_exists('optional', $route)) {
            $route['mask'] = '#^';
            for ($i = 0; $i < $route['optional']; ++$i) {
                $route['mask'] .= '/'.$route['parts'][$i];
            }
            $route['mask'] .= '(/(.*))?$#';
        } else {
            $route['mask'] = "#^/".implode('/', $route['parts'])."$#";
        }
        return $this;
    }

    public function __call($verb, $args)
    {
        if (!array_key_exists($verb, $this->methods)) {
            new RouterException(9, ['->'.$verb.'()']);
            return $this;
        }

        if (!isset($args[1])) {
            new RouterException(16, ['->'.$verb.'()']); // to do
            return $this;
        }

        $path = $args[0];
        $action = $args[1];
        isset($args[2]) ? $controller = $args[2] : $controller = null;

        //добавляем префиксы к пути и нормализуем последний '/'
        $nodes[] = '';
        foreach ($this->urlNodes as $File) {
            foreach ($File as $Node) {
                $nodes[] = $Node;
            }
        }
        $fullPath = implode('', $nodes);
        $fullPath .= $path;
        $fullPath == '/' ?: $fullPath = rtrim($fullPath, '/');

        if (!isset($this->routes[$fullPath])) {
            $this->routes[$fullPath] = $this->parsePath($fullPath);
            $this->routes[$fullPath]['path'] = $fullPath;
        } elseif (isset($this->routes[$fullPath]['verbs'][$verb])) {
            new RouterException(14, ['->'."$verb('".$action."')"], 1);// переделать
            return $this;
        }

        //проверка и добавление контроллера и действия
        $method = &$this->routes[$fullPath]['verbs'][$verb];
        if ($controller) {
            $method['controller'] = end($this->controllerSpace).$controller;
        } elseif ($this->controllerGroup) {
            $method['controller'] = end($this->controllerSpace).$this->controllerGroup;
        } else {
            new RouterException(3, [$path, 'route()']);// переделать
            return $this;
        }
        $method['action'] = $action;

        if ($this->safeMode) {
            $file = debug_backtrace()[0]['file'].'::'.debug_backtrace()[0]['line'];
            if (!isset($this->routes[$fullPath]['file'])) {
                $this->routes[$fullPath]['file'] = $file;
            } else {
                strcmp($this->routes[$fullPath]['file'], $file) < 0
                    ? $count = strlen($this->routes[$fullPath]['file'])
                    : $count = strlen($file);

                $delimiter = 0;
                for ($i = 0; $i < $count; ++$i) {
                    if (($this->routes[$fullPath]['file'][$i] == "/") && $file[$i] == "/") {
                        $prevDelimiter = $delimiter;
                        $delimiter = $i;
                    }
                    if (($this->routes[$fullPath]['file'][$i] == ":") && $file[$i] == ":") {
                        $delimiter = $i;
                    }
                    if ($this->routes[$fullPath]['file'][$i] !== $file[$i]) {
                        if (strpos(substr(explode('::', $this->routes[$fullPath]['file'])[0],
                            $delimiter + 1), '/')) {
                            $delimiter = $prevDelimiter;
                        }
                        $this->routes[$fullPath]['file']
                            .= substr($file, $delimiter, strlen($file));
                        break;
                    }
                }
            }
        }

        $type = $this->routes[$fullPath]['type'];
        $this->$type[$fullPath] = &$this->routes[$fullPath];

        end($this->routes);
        $this->last = 'route';

        return $this;
    }

    private function parsePath($fullPath)
    {
        if (!strpos($fullPath, '{')) {
            $arr['type'] = 'simple';
        } else {
            $arr['type'] = 'regex';
            $parts = preg_split('#/#', $fullPath, -1, PREG_SPLIT_NO_EMPTY);
            $arr['mask'] = '#^';
            $optional = 0;

            for ($i = 0; $i < count($parts); ++$i) {
                if (preg_match('/^{.+}$/', $parts[$i])) {
                    $param = trim($parts[$i], '{}');
                    $param = preg_replace('/\?/', '', $param, 1, $opt);

                    if ($opt) {
                        if (++$optional == 1) {
                            $arr['optional'] = $i;
                        }
                    }
                    if (!$optional) {
                        $arr['mask'] .= '/.+';
                    }
                    $arr['parts'][$i] = '.+';

                    if (isset($arr['params'][$param])) {
                        new RouterException(1, [$param, $fullPath]);
                    }
                    $arr['params'][$param] = $i;
                } else {
                    $arr['mask'] .= '/'.$parts[$i];
                    $arr['parts'][$i] = $parts[$i];
                }
            }
            $optional
                ? $arr['mask'] .= '(/(.*))?$#'
                : $arr['mask'] .= '$#';
        }
        return $arr;
    }


    private function name($name = null)
    {
        if (!$name) {
            new RouterException(16, [__FUNCTION__.'()']);
            return $this;
        }
        if ($this->last != 'route') {
            new RouterException(7, ["name('".$name."')"]);
            return $this;
        }
        $route = &$this->routes[key($this->routes)];

        if ($this->safeMode && array_key_exists('name', $route)) {
            new RouterException(22, ["name('$name')"]);
            return $this;
        }
        foreach ($this->nameSpace as $File) {
            foreach ($File as $NameSpaceValue) {
                $spaceParts[] = $NameSpaceValue;
            }
        }
        if (isset($spaceParts)) {

            $nSpace = implode('/', $spaceParts);
            $fullSpace = $nSpace.'/'.$name;

            if ($this->safeMode && array_key_exists($fullSpace, $this->name)) {
                new RouterException(2, [$fullSpace]);
                return $this;
            }
            $route['nSpace'] = $nSpace;

            $this->name[$fullSpace] = &$route;
        } else {
            $this->name[$name] = &$route;
        }
        $route['name'] = $name;


        return $this;
    }


    private function overflow()
    {
        if (!$this->safeMode) {
            return $this;
        }
        if ($this->last != 'route') {
            new RouterException(15, ['overflow()', 'route()']);
            return $this;
        }
        $lastRoure = &$this->routes[key($this->routes)];
        if (array_key_exists('mask', $lastRoure)) {
            new RouterException(18, ['owerflow()']);
            return $this;
        }
        $lastRoure['overflow'] = '';

        return $this;
    }

    private function checkGroup(&$group, $groupName)
    {
        $lastFile = key($group);
        if (!empty($group[$lastFile])) {
            $this->groupPop($lastFile, $group, $groupName);
        }
        prev($group);
    }

    private function groupPop($lastFile, &$group, $groupName)
    {
        if ($groupValue = array_pop($group[$lastFile])) {
            new RouterException(5, [$groupName.'(\''.$groupValue.'\')'], $lastFile);
            $this->groupPop($lastFile, $group, $groupName);
        }
    }

    private function checkRegex()
    {
        foreach ($this->methods as $method) {
            if (isset($this->simple[$method])) {
                foreach ($this->simple[$method] as $simple) {
                    if (isset($this->regex[$method])) {
                        foreach ($this->regex[$method] as $regex) {
                            if (!array_key_exists('overflow', $simple) &&
                                preg_match($regex['mask'], $simple['route'])
                            ) {
                                new RouterException(17, [
                                    $simple['route'],
                                    $regex['route'],
                                    $method,
                                ], $regex['mask']);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Меняет стандартную страницу 404
     *
     *
     * @param  string $controller контроллер
     * @param  string $action действие
     * @return object             $this
     */
    private function page404($controller = null, $action = null)
    {
        if (!$controller) {
            new RouterException(16, [__FUNCTION__.'()']);
            return $this;
        }
        if ($this->safeMode) {

            $this->match404['file']
                =
                debug_backtrace()[0]['file'].'::'.debug_backtrace()[0]['line'];
        }
        $this->match404['controller'] = $controller;
        $this->match404['action'] = $action;

        return $this;
    }


    /**
     * Находит  маршрут по URL и методу.
     *
     * Сначала поиск происходит по индексу простых URL
     * дале по индексу URL содержащих параметры.
     * Если не найден URL возвращается массив с ключом 'code404',
     * если наиден URL и в нем не наден метод - 'code405'
     *
     * @param string $url часть URL, только URL-путь
     * @param string $method HTTP метод
     * @return array
     */
    public function matchUrl($url, $method)
    {
        $method = strtolower($method);
        $url == '/' ?: $url = rtrim($url, '/');

        if (isset($this->simple[$url])) {

            if (!isset($this->simple[$url]['verbs'][$method])) {
                return $this->match405($this->simple[$url]);
            }

            isset($this->simple[$url]['nSpace'])
                ? $nSpace = $this->simple[$url]['nSpace']
                : $nSpace = '';

            return [
                'code'       => 200,
                'controller' => $this->simple[$url]['verbs'][$method]['controller'],
                'action'     => $this->simple[$url]['verbs'][$method]['action'],
                'nSpace'     => $nSpace,
                'params'     => []
            ];
        }

        foreach ($this->regex as $regexRoute) {
            if (!preg_match($regexRoute['mask'], $url)) continue;

            if (!isset($regexRoute[$method])) {
                return $this->match405($regexRoute);
            }
            $urlParts = explode('/', ltrim($url, '/'));

            if (isset($regexRoute['optional'])) {
                if (($count = count($urlParts)) > count($regexRoute['parts'])) continue;

                for ($i = $regexRoute['optional']; $i < $count; ++$i) {
                    if (!preg_match('#^'.$regexRoute['parts'][$i].'$#', $urlParts[$i])) {
                        return $this->match404;
                    }
                }
            }
            $params = [];
            foreach ($regexRoute['params'] as $key => $value) {
                if (isset($urlParts[$value])) {
                    $params[$key] = $urlParts[$value];
                } else {
                    break;
                }
            }

            isset($regexRoute['nSpace'])
                ? $nSpace = $regexRoute['nSpace']
                : $nSpace = '';

            return [
                'code'       => 200,
                'controller' => $regexRoute['verbs'][$method]['controller'],
                'action'     => $regexRoute['verbs'][$method]['action'],
                'params'     => $params,
                'nSpace'     => $nSpace,
            ];
        }
        return $this->match404;
    }

    private function match405(array $route)
    {
        $arr = [];
        if (isset($route['verbs']['get']) && isset($this->methods['head'])) {
            $arr[] = 'head';
        }
        $arr = array_merge($arr, array_keys($route['verbs']));

        array_walk($arr, function(&$item, $key) {
            $item = strtoupper($item);
        });

        return [
            'code'  => 405,
            'allow' => implode(',', $arr)
        ];
    }

    private function list($url = null)
    {
        if (!$url) {
            new RouterException(16, [__FUNCTION__.'()']);
            return $this;
        }
        $requestUri = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
        if ($requestUri != $url) {
            return $this;
        }
        if ($this->matchUrl($url, 'GET')['code'] == 404) {
            include __DIR__.'/list/list.php';
            die();
        }
    }

    public function getByNamespace($namespace)
    {
        if (array_key_exists($namespace, $this->name)) {
            return $this->name[$namespace];
        } else {
            return false;
        }
    }
}
