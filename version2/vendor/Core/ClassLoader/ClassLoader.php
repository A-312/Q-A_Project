<?php
namespace Core\ClassLoader;

class ClassLoader {
	private $prefixes = array();
	private $fallbackDirs = array();
	private $useIncludePath = false;
	private $classMap = array();

	public function getPrefixes() {
		return call_user_func_array("array_merge", $this->prefixes);
	}

	public function getFallbackDirs() {
		return $this->fallbackDirs;
	}

	public function getClassMap() {
		return $this->classMap;
	}

	public function addClassMap(array $classMap) {
		if ($this->classMap) {
			$this->classMap = array_merge($this->classMap, $classMap);
		} else {
			$this->classMap = $classMap;
		}
	}

	public function add($prefix, $paths, $prepend = false) {
		if (!$prefix) {
			if ($prepend) {
				$this->fallbackDirs = array_merge(
					(array) $paths,
					$this->fallbackDirs
				);
			} else {
				$this->fallbackDirs = array_merge(
					$this->fallbackDirs,
					(array) $paths
				);
			}

			return;
		}

		$first = $prefix[0];
		if (!isset($this->prefixes[$first][$prefix])) {
			$this->prefixes[$first][$prefix] = (array) $paths;

			return;
		}
		if ($prepend) {
			$this->prefixes[$first][$prefix] = array_merge(
				(array) $paths,
				$this->prefixes[$first][$prefix]
			);
		} else {
			$this->prefixes[$first][$prefix] = array_merge(
				$this->prefixes[$first][$prefix],
				(array) $paths
			);
		}
	}

	public function set($prefix, $paths) {
		if (!$prefix) {
			$this->fallbackDirs = (array) $paths;

			return;
		}
		$this->prefixes[substr($prefix, 0, 1)][$prefix] = (array) $paths;
	}

	public function setUseIncludePath($useIncludePath) {
		$this->useIncludePath = $useIncludePath;
	}

	public function getUseIncludePath() {
		return $this->useIncludePath;
	}

	public function register($prepend = false) {
		spl_autoload_register(array($this, "loadClass"), true, $prepend);
	}

	public function unregister() {
		spl_autoload_unregister(array($this, "loadClass"));
	}

	public function loadClass($class) {
		if ($file = $this->findFile($class)) {
			include $file;

			return true;
		}
	}

	public function findFile($class) {
		if ("\\" == $class[0]) {
			$class = substr($class, 1);
		}

		if (isset($this->classMap[$class])) {
			return $this->classMap[$class];
		}

		if (false !== $pos = strrpos($class, "\\")) {
			$classPath = strtr(substr($class, 0, $pos), "\\", DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		} else {
			$classPath = null;
			$className = $class;
		}

		$classPath .= strtr($className, "_", DIRECTORY_SEPARATOR) . ".php";

		$first = $class[0];
		if (isset($this->prefixes[$first])) {
			foreach ($this->prefixes[$first] as $prefix => $dirs) {
				if (0 === strpos($class, $prefix)) {
					foreach ($dirs as $dir) {
						if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
							return $dir . DIRECTORY_SEPARATOR . $classPath;
						}
					}
				}
			}
		}

		foreach ($this->fallbackDirs as $dir) {
			if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
				return $dir . DIRECTORY_SEPARATOR . $classPath;
			}
		}

		if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
			return $file;
		}

		return $this->classMap[$class] = false;
	}
}
