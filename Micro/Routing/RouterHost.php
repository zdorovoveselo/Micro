<?php
namespace MicroMir\Routing;

use MicroMir\Http\Verbs;

class RouterHost
{
	private $routers = [];

	private $hosts = [];

	private $lastRouter;
    

    public function __construct(Verbs $verbs, $Route)
    {
//        c()->Verb;

        $this->Verbs = $verbs;

		$RouterHost = $this;

		include MICRO_HOSTS_SETTINGS;

		return $this;
    }

	private function router($name = '')
	{
		if (array_key_exists($name, $this->routers)) {
			new RouterHostException(0, ['->router(\''.$name.'\')']);
			return $this;
		}
	    $this->routers[$name] = [];
	    $this->lastRouter = &$this->routers[$name];

	    return $this;
	}

	private function list(array $path, $safe = 1)
	{
		if (array_key_exists('path', $this->lastRouter)) {
			end($this->routers);
			new RouterHostException(1, [key($this->routers), '->list(..)']);
			return $this;
		}
		$this->lastRouter['path'] = $path;
		$this->lastRouter['safe'] = $safe;

		return $this;
	}

	private function host($host = '')
	{
		if (array_key_exists($host, $this->hosts)) {
			new RouterHostException(2, ['->host(\''.$host.'\')']);
			return $this;
		}
		$this->hosts[$host] = &$this->lastRouter;
		$this->hosts[$host]['host'] = $host;

		return $this;
	}

	public function getRouterByHost($host = null)
	{
		if (1 == count($this->routers)) {
			return $this->getRouter($this->lastRouter);
		}
		if (array_key_exists($host, $this->hosts)) {
			return $this->getRouter($this->hosts[$host]);
		}
		if (array_key_exists('*', $this->hosts)) {
			return $this->getRouter($this->hosts['*']);
		}
	}

	public function getRouterByName($name)
	{
		if (array_key_exists($name, $this->routers)) {
			return $this->getRouter($this->routers[$name]);
		}
		new RouterHostException(3, [$name]);
	}


	private function getRouter(&$router)
	{
		if (! array_key_exists('object', $router)) {

			$router['object']
			=
			new Router($this->Verbs->array, $router['path'], $router['safe']);
		}
		return $router['object'];
	}
}
