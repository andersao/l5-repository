<?php
namespace Prettus\Repository\Generators;

/**
 * Class BindingsGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class BindingsGenerator extends Generator
{

    /**
     * The placeholder for repository bindings
     *
     * @var string
     */
    public $bindPlaceholder = '//:end-bindings:';
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'bindings/bindings';

    public function run()
    {
        // Add entity repository binding to the repository service provider
        $provider = \File::get($this->getPath());
        $repositoryInterface = '\\' . $this->getRepository() . "::class";
        $repositoryEloquent = '\\' . $this->getEloquentRepository() . "::class";
        \File::put($this->getPath(), str_replace($this->bindPlaceholder, "\$this->app->bind({$repositoryInterface}, $repositoryEloquent);" . PHP_EOL . '        ' . $this->bindPlaceholder, $provider));
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        $default_path = app()->path() . "/Providers/";
        return config("repository.generator.provider", $default_path) . '/RepositoryServiceProvider.php';
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return app()->path();
    }

    /**
     * Get generator path config node.
     *
     * @return string
     */
    public function getPathConfigNode()
    {
        return '';
    }

    /**
     * Gets repository full class name
     *
     * @return string
     */
    public function getRepository()
    {
        $repositoryGenerator = new RepositoryInterfaceGenerator([
            'name' => $this->name,
            'module' => $this->module,
        ]);

        $repository = $repositoryGenerator->getRootNamespace() . '\\' . $repositoryGenerator->getName();
        return str_replace([
            "\\",
            '/'
        ], '\\', $repository) . 'RepositoryInterface';
    }

    /**
     * Gets eloquent repository full class name
     *
     * @return string
     */
    public function getEloquentRepository()
    {
        $repositoryGenerator = new RepositoryEloquentGenerator([
            'name' => $this->name,
            'module' => $this->module,
        ]);

        $repository = $repositoryGenerator->getRootNamespace() . '\\' . $repositoryGenerator->getName();
        return str_replace([
            "\\",
            '/'
        ], '\\', $repository) . ('Repository' . $this->getORM());
    }

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        $default_namespace = app()->getNamespace() . "\Providers";
        return str_replace([
            "\\",
            '/'
        ], '\\', config("repository.generator.provider", $default_namespace));
//        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [
            'repository' => $this->getRepository(),
            'eloquent' => $this->getEloquentRepository(),
            'placeholder' => $this->bindPlaceholder,
        ]);
    }
}
