<?php

declare(strict_types = 1);

class UniversalClassLoader
{

    protected array $prefixes = [];

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = [];
        }
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            $this->prefixes[$prefix][] = $base_dir;
        }
    }

    public function loadClass($fullyQualifiedClassName)
    {
        $prefix = $fullyQualifiedClassName;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($fullyQualifiedClassName, 0, $pos + 1);
            $relative_class = substr($fullyQualifiedClassName, $pos + 1);
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }
            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    protected function loadMappedFile($prefix, $relative_class)
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir
                    . str_replace('\\', '/', $relative_class)
                    . '.php';
            if ($this->requireFile($file)) {
                return $file;
            }
        }
        return false;
    }

    protected function requireFile($file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
