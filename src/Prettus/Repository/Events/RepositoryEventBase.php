<?php

namespace Prettus\Repository\Events;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class RepositoryEventBase
 *
 * @package Prettus\Repository\Events
 * @author  Anderson Andrade <contato@andersonandra.de>
 */
abstract class RepositoryEventBase
{
    /**
     * @var Model|null
     */
    protected ?Model $model;

    /**
     * @var array
     */
    protected array $attributes = [];

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
     * @param Model|null          $model
     * @param array               $attributes
     */
    public function __construct(RepositoryInterface $repository, ?Model $model = null, array $attributes = [])
    {
        $this->repositoryClass = get_class($repository);
        $this->model           = $model;
        $this->attributes      = $attributes;
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
