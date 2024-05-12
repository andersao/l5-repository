<?php

namespace Prettus\Repository\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Class Generator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
abstract class Generator
{

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The array of options.
     *
     * @var array
     */
    protected $options;

    /**
     * The shortname of stub.
     *
     * @var string
     */
    protected $stub;

    /**
     * The structure type and list of the modules
     *
     * @var string
     */
    protected $structure, $modules, $ORM;

    /**
     * Create new instance of this class.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->filesystem = new Filesystem;
        $this->options = $options;
        $this->structure = config('repository.generator.structure');
        $this->modules = config('repository.generator.modules');
        $this->ORM = config("repository.generator.ORM", 'Eloquent');
    }


    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }


    /**
     * Set the filesystem instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * get the stub name
     *
     * @return string
     */
    public function getStubName()
    {
        return $this->stub;
    }

    /**
     * get the ORM Type
     *
     * @return string
     */
    public function getORM()
    {
        return ucfirst(strtolower(config("repository.generator.ORM", 'Eloquent')));
    }

    /**
     * Get stub template for generated file.
     *
     * @return string
     */
    public function getStub()
    {
        $path = config('repository.generator.stubsOverridePath', __DIR__);

        if (!file_exists($path . '/Stubs/' . $this->getStubName() . '.stub')) {
            $path = __DIR__;
        }

        return (new Stub($path . '/Stubs/' . $this->getStubName() . '.stub', $this->getReplacements()))->render();
    }


    /**
     * Get template replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return [
            'class' => $this->getClass(),
            'namespace' => $this->getNamespace(),
            'root_namespace' => $this->getRootNamespace()
        ];
    }


    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return base_path();
    }


    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . $this->getName() . '.php';
    }


    /**
     * Get name input.
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->name;
        if (Str::contains($this->name, '\\')) {
            $name = str_replace('\\', '/', $this->name);
        }
        if (Str::contains($this->name, '/')) {
            $name = str_replace('/', '/', $this->name);
        }

        return Str::studly(str_replace(' ', '/', ucwords(str_replace('/', ' ', $name))));
    }


    /**
     * Get application namespace
     *
     * @return string
     */
    public function getAppNamespace()
    {
        return \Illuminate\Container\Container::getInstance()->getNamespace();
    }


    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return Str::studly(class_basename($this->getName()));
    }


    /**
     * Get paths of namespace.
     *
     * @return array
     */
    public function getSegments()
    {
        return explode('/', $this->getName());
    }


    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return config('repository.generator.rootNamespace', $this->getAppNamespace());
    }


    /**
     * Get class-specific output paths.
     *
     * @param $class
     *
     * @return string
     */
    public function getConfigGeneratorClassPath($class, $directoryPath = false)
    {
        switch ($class) {
            case ('models' === $class):
                $path = config('repository.generator.paths.models', 'Entities');
                break;
            case ('repositories' === $class):
                $path = config('repository.generator.paths.repositories', 'Repositories');
                break;
            case ('interfaces' === $class):
                $path = config('repository.generator.paths.interfaces', 'Repositories');
                break;
            case ('presenters' === $class):
                $path = config('repository.generator.paths.presenters', 'Presenters');
                break;
            case ('transformers' === $class):
                $path = config('repository.generator.paths.transformers', 'Transformers');
                break;
            case ('validators' === $class):
                $path = config('repository.generator.paths.validators', 'Validators');
                break;
            case ('controllers' === $class):
                $path = config('repository.generator.paths.controllers', 'Http\Controllers');
                break;
            case ('criteria' === $class):
                $path = config('repository.generator.paths.criteria', 'Criteria');
                break;
            case ('migrations' === $class):
                $path = config('repository.generator.paths.migrations', 'database\migrations');
                break;
            default:
                $path = '';
        }

        // CHECKING IF WE HAVE MODULAR STRUCTURE
        $is_module = false;
        if($this->structure == 'modular' && !empty($this->modules) && in_array($this->module, $this->modules)){
            $is_module = true;
        }

        if ($directoryPath) {
            $path = str_replace('\\', '/', $path);
            $path = $is_module ? ($this->module . "/" . $path) : $path;
        } else {
            $path = str_replace('/', '\\', $path);
            $path = $is_module ? ($this->module . "\\" . $path) : $path;
        }

        return $path;
    }


    abstract public function getPathConfigNode();


    /**
     * Get class namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        $segments = $this->getSegments();
        array_pop($segments);
        $rootNamespace = $this->getRootNamespace();
        if ($rootNamespace == false) {
            return null;
        }

        return 'namespace ' . rtrim($rootNamespace . '\\' . implode('\\', $segments), '\\') . ';';
    }


    /**
     * Setup some hook.
     *
     * @return void
     */
    public function setUp()
    {
        //
    }


    /**
     * Run the generator.
     *
     * @return int
     * @throws FileAlreadyExistsException
     */
    public function run()
    {
        $this->setUp();
        if ($this->filesystem->exists($path = $this->getPath()) && !$this->force) {
            throw new FileAlreadyExistsException($path);
        }
        if (!$this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0777, true, true);
        }

        return $this->filesystem->put($path, $this->getStub());
    }


    /**
     * Get options.
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Determinte whether the given key exist in options array.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }


    /**
     * Get value from options by given key.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function getOption($key, $default = null)
    {
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key] ?: $default;
    }


    /**
     * Helper method for "getOption".
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string
     */
    public function option($key, $default = null)
    {
        return $this->getOption($key, $default);
    }


    /**
     * Handle call to __get method.
     *
     * @param string $key
     *
     * @return string|mixed
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->option($key);
    }
}
