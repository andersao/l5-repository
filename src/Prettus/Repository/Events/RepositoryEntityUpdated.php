<?php
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 * @package Prettus\Repository\Events
 */
class RepositoryEntityUpdated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "updated";
}