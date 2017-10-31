<?php
namespace Prettus\Repository\Generators;

/**
 * Class PresenterGenerator
 * @package Prettus\Repository\Generators
 */

class PresenterGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'presenter/presenter';

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }

    /**
     * Get generator path config node.
     *
     * @return string
     */
    public function getPathConfigNode()
    {
        return 'presenters';
    }

    /**
     * Get parent class config node.
     *
     * @return string
     */
    public function getParentClassConfigNode()
    {
        return 'presenter';
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        $transformerGenerator = new TransformerGenerator([
            'name' => $this->name
        ]);
        $transformer = $transformerGenerator->getRootNamespace() . '\\' . $transformerGenerator->getName() . 'Transformer';
        $transformer = str_replace([
            "\\",
            '/'
        ], '\\', $transformer);
        echo $transformer;

        return array_merge(parent::getReplacements(), [
            'transformer' => $transformer,
            'parent_class'    => $this->getParentClass(),
            'parent_class_name'    => $this->getParentClassName(),
        ]);
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getName() . 'Presenter.php';
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return config('repository.generator.basePath', app_path());
    }
}
