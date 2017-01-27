<?php

namespace Bolt\Configuration;

use Webmozart\PathUtil\Path;

/**
 * A class to resolve and manage paths. Paths defined here are allowed to have variables within them.
 * For example: "files" folder is within the web directory so it is defined as "%web%/files". This allows
 * the web directory to be changed and the files path does not have to be redefined.
 *
 * This functionality could be added within ResourceManager, but given that 90% of that code is deprecated I figured
 * it would be better to create this separately.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
class PathResolver
{
    /** @var array */
    protected $paths = [];

    /**
     * Default paths for Bolt installation.
     *
     * @return array
     */
    public static function defaultPaths()
    {
        return [
            'cache'             => 'app/cache',
            'config'            => 'app/config',
            'database'          => 'app/database',
            'extensions'        => 'extensions',
            'extensions_config' => '%config%/extensions',
            'web'               => 'public',
            'files'             => '%web%/files',
            'themes'            => '%web%/theme',
            'bolt_assets'       => '%web%/bolt-public/view',
        ];
    }

    /**
     * Constructor.
     *
     * @param string $root  The root path which must be absolute.
     * @param array  $paths Initial path definitions.
     */
    public function __construct($root, $paths = [])
    {
        foreach ($paths as $name => $path) {
            $this->define($name, $path);
        }

        $root = Path::canonicalize($root);

        if (Path::isRelative($root)) {
            throw new \InvalidArgumentException('Root path must be absolute.');
        }

        $this->paths['root'] = $root;
    }

    /**
     * Define a path, or really an alias/variable.
     *
     * @param string $name
     * @param string $path
     */
    public function define($name, $path)
    {
        $name = $this->normalizeName($name);

        $this->paths[$name] = $path;
    }

    /**
     * Resolve a path.
     *
     * Examples:
     *  - `%web%/files` - A path with variables.
     *  - `files` - A previously defined variable.
     *  - `foo/bar` - A relative path that will be resolved against the root path.
     *  - `/tmp` - An absolute path will be returned as is.
     *
     * @param string $path     The path.
     * @param bool   $absolute If the path is relative, resolve it against the root path.
     *
     * @return string
     */
    public function resolve($path, $absolute = true)
    {
        $path = $this->normalizeName($path);

        if (isset($this->paths[$path])) {
            $path = $this->paths[$path];
        }

        $path = preg_replace_callback('#%(.+)%#', function ($match) use ($path) {
            $alias = $match[1];

            if (!isset($this->paths[$this->normalizeName($alias)])) {
                throw new \InvalidArgumentException("Failed to resolve path. Alias %$alias% is not defined.");
            }

            // absolute if alias is at start of path
            $absolute = strpos($path, "%$alias%") === 0;

            return $this->resolve($alias, $absolute);
        }, $path);

        if ($absolute && Path::isRelative($path)) {
            $path = Path::makeAbsolute($path, $this->paths['root']);
        }

        // Not necessary, could remove.
        $path = Path::canonicalize($path);

        return $path;
    }

    /**
     * Returns the raw path definition for the name given.
     *
     * @param string $name
     *
     * @return string|null
     */
    public function raw($name)
    {
        $name = $this->normalizeName($name);

        return isset($this->paths[$name]) ? $this->paths[$name] : null;
    }

    /**
     * Resolves and returns all known paths.
     *
     * @return array
     */
    public function resolveAll()
    {
        return array_map(function ($path) {
            return $this->resolve($path);
        }, $this->paths);
    }

    /**
     * For BC.
     *
     * @deprecated since 3.3, will be removed in 4.0.
     *
     * @param string $name
     *
     * @return string
     */
    private function normalizeName($name)
    {
        if ($name === 'themebase') {
            return 'themes';
        }
        if ($name === 'extensionsconfig') {
            return 'extensions_config';
        }
        if ($name === 'view') {
            return 'bolt_assets';
        }

        return $name;
    }
}
