<?php
namespace Prettus\Repository\Generators;

/**
 * Class TransformerGenerator
 * @package Prettus\Repository\Generators
 */

class TransformerGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'transformer/transformer';

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return config('repository.generator.basePath', app_path());
    }

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return parent::getRootNamespace().'Transformers\\';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/Transformers/' . $this->getName() . 'Transformer.php';
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        $modelGenerator = new ModelGenerator();
        return array_merge(parent::getReplacements(),[
            'model_namespace' => $modelGenerator->getRootNamespace()
        ]);
    }
}