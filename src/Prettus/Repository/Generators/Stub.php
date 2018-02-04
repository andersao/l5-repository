<?php

namespace Prettus\Repository\Generators;

/**
 * Class Stub
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class Stub
{
    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static $basePath = null;
    /**
     * The stub path.
     *
     * @var string
     */
    protected $path;
    /**
     * The replacements array.
     *
     * @var array
     */
    protected $replaces = [];

    /**
     * The contructor.
     *
     * @param string $path
     * @param array  $replaces
     */
    public function __construct($path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    /**
     * Create new self instance.
     *
     * @param  string $path
     * @param  array  $replaces
     *
     * @return self
     */
    public static function create($path, array $replaces = [])
    {
        return new static($path, $replaces);
    }

    /**
     * Set base path.
     *
     * @param  string $path
     *
     * @return void
     */
    public static function setBasePath($path)
    {
        static::$basePath = $path;
    }

    /**
     * Set replacements array.
     *
     * @param  array $replaces
     *
     * @return $this
     */
    public function replace(array $replaces = [])
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * Get replacements.
     *
     * @return array
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get stub contents.
     *
     * @return string
     */
    public function render()
    {
        return $this->getContents();
    }

    /**
     * Get stub contents.
     *
     * @return mixed|string
     */
    public function getContents()
    {
        $contents = file_get_contents($this->getPath());
        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$' . strtoupper($search) . '$', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get stub path.
     *
     * @return string
     */
    public function getPath()
    {
        return static::$basePath . $this->path;
    }

    /**
     * Set stub path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
