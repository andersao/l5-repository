<?php

namespace Prettus\Repository\Generators;

class MemoizedGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @return string
     */
    public function getStub(): string
    {
        $path = config('repository.generator.stubsOverridePath');

        $stub = file_get_contents($path . '/Stubs/Repository/memoized.stub');

        return str_replace(
            ['{{ repositoryNamespace }}', '{{ repositoryName }}', '{{ lcfirstRepositoryName }}', '{{ memoizedName }}'],
            ['App\Traits\Repository\Memoized', $this->setRepository(), $this->setRepositoryLcFirst(), $this->setMemoized()],
            $stub
        );
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return base_path() . '/app/Traits/Repository/Memoized/';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . $this->getFileName() . '.php';
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
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return '';
    }

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->name . 'RepositoryMemoized';
    }

    /**
     * set Memoized.
     */
    private function setMemoized()
    {
        return $this->name . 'RepositoryMemoized';
    }

    /**
     * set Repository.
     */
    private function setRepository()
    {
        return $this->name . 'Repository';
    }

    /**
     * set RepositoryLcFirst.
     */
    private function setRepositoryLcFirst(): string
    {
        return lcfirst($this->setRepository());
    }
}
