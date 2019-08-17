<?php
namespace Core\Kernel\Bundle;

class Bundle {
	private $name;
	protected $path;
	protected $class;
	protected $map;

	public function boot($dev = true) {
		if ($dev) { return $this->bootdev(); }
	}

	public function bootdev() {

	}

	public function build() {

	}

	public function onKernelRequest() {

	}

	public function onKernelFinishRequest() {

	}

	public function terminate() {

	}

	public function getClass() {
		return $this->class;
	}

	public function getPath() {
		if (null === $this->path) {
			$reflected = new \ReflectionObject($this->class ?: $this);
			$this->path = dirname($reflected->getFileName());
		}

		return $this->path;
	}

	public function getNamespace() {
		$class = get_class($this);

		return substr($class, 0, strrpos($class, "\\"));
	}

	final public function getName() {
		if (null !== $this->name) {
			return $this->name;
		}

		$name = get_class($this);
		$pos = strrpos($name, "\\");

		return $this->name = false === $pos ? $name : substr($name, $pos + 1);
	}

	public function getMap() {
		if (null === $this->map) {
			$this->map = array($this->class ?: $this);
		}

		return $this->map;
	}
}
