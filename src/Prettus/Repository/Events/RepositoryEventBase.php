<?php
namespace Prettus\Repository\Events;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class RepositoryEventBase
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
abstract class RepositoryEventBase
{
    /**
     * @var Model|null
     */
    protected ?Model $model;

    /**
     * @var string
     */
    protected string $repositoryClass;

    /**
     * @var string
     */
    protected string $action;

    /**
     * @param RepositoryInterface $repository
     * @param Model|null               $model
     */
    public function __construct(RepositoryInterface $repository, ?Model $model = null)
    {
        $this->repositoryClass = get_class($repository);
        $this->model = $model;
    }

    /**
     * @return Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
