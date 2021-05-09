<?php

namespace Prettus\Repository\Tests\Concerns;

use Illuminate\Support\Facades\File;

/**
 * Trait CommandTestTrait
 *
 * @package Prettus\Repository\Tests\Concerns
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
trait CommandTestTrait
{
    /**
     * @var string
     */
    protected $fooRepositoryEloquentClass;

    /**
     * @var string
     */
    protected $fooRepositoryClass;

    /**
     * @var string
     */
    protected $fooPresenterClass;

    /**
     * @var string
     */
    protected $fooControllerClass;

    /**
     * @var string
     */
    protected $fooValidatorClass;

    /**
     * @var string
     */
    protected $fooModelClass;

    /**
     * @var string
     */
    protected $fooTransformerClass;

    /**
     * @var string
     */
    protected $fooCriteriaClass;

    /**
     * Get test stub for transformer
     *
     * @return string
     */
    protected function controllerStub()
    {
        return __DIR__.'/../Commands/Stubs/FoosController.stub';
    }

    /**
     * Get test stub for transformer
     *
     * @return string
     */
    protected function modelStub()
    {
        return __DIR__.'/../Commands/Stubs/FooModel.stub';
    }

    /**
     * Get test stub for transformer
     *
     * @return string
     */
    protected function transformerStub()
    {
        return __DIR__.'/../Commands/Stubs/FooTransformer.stub';
    }

    /**
     * Get test stub for presenter
     *
     * @return string
     */
    protected function presenterStub()
    {
        return __DIR__.'/../Commands/Stubs/FooPresenter.stub';
    }

    /**
     * Get test stub for validator
     *
     * @return string
     */
    protected function validatorStub()
    {
        return __DIR__.'/../Commands/Stubs/FooValidator.stub';
    }

    /**
     * Get test stub for repository
     *
     * @return string
     */
    protected function repositoryEloquentStub()
    {
        return __DIR__.'/../Commands/Stubs/FooRepositoryEloquent.stub';
    }

    /**
     * Get test stub for repository
     *
     * @return string
     */
    protected function repositoryStub()
    {
        return __DIR__.'/../Commands/Stubs/FooRepository.stub';
    }

    /**
     * Get test stub for criteria
     *
     * @return string
     */
    protected function criteriaStub()
    {
        return __DIR__.'/../Commands/Stubs/FooCriteria.stub';
    }

    /**
     * Prepare criteria class path
     *
     * @return void
     */
    public function prepCriteriaClass()
    {
        if (File::exists($this->fooCriteriaClass = app_path('Criteria/FooCriteria.php'))) {
            unlink($this->fooCriteriaClass);
        }
    }

    /**
     * Prepare repository class paths
     *
     * @return void
     */
    protected function prepRepositoryClasses()
    {
        if (File::exists($this->fooRepositoryEloquentClass = app_path('Repositories/FooRepositoryEloquent.php'))) {
            unlink($this->fooRepositoryEloquentClass);
        }

        if (File::exists($this->fooRepositoryClass = app_path('Repositories/FooRepository.php'))) {
            unlink($this->fooRepositoryClass);
        }
    }

    /**
     * Prepare controller class path
     *
     * @return void
     */
    public function prepControllerClass()
    {
        if (File::exists($this->fooControllerClass = app_path('Http/Controllers/FoosController.php'))) {
            unlink($this->fooControllerClass);
        }
    }

    /**
     * Prepare presenter class path
     *
     * @return void
     */
    public function prepPresenterClass()
    {
        if (File::exists($this->fooPresenterClass = app_path('Presenters/FooPresenter.php'))) {
            unlink($this->fooPresenterClass);
        }
    }

    /**
     * Prepare validator class path
     *
     * @return void
     */
    public function prepValidatorClass()
    {
        if (File::exists($this->fooValidatorClass = app_path('Validators/FooValidator.php'))) {
            unlink($this->fooValidatorClass);
        }
    }

    /**
     * Prepare model class path
     *
     * @return void
     */
    public function prepModelClass()
    {
        if (File::exists($this->fooModelClass = app_path('Entities/Foo.php'))) {
            unlink($this->fooModelClass);
        }
    }

    /**
     * Prepare transformer class path
     *
     * @return void
     */
    public function prepTransformerClass()
    {
        if (File::exists($this->fooTransformerClass = app_path('Transformers/FooTransformer.php'))) {
            unlink($this->fooTransformerClass);
        }
    }
}
