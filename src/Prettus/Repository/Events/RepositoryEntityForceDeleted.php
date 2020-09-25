<?php

namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityForceDeleted
 * @package Prettus\Repository\Events
 * @author Roberto Arruda <robertoadearruda@gmail.com>
 */
class RepositoryEntityForceDeleted extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "forceDeleted";
}
