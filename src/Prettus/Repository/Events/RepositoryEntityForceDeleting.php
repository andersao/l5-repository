<?php

namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityForceDeleting
 * @package Prettus\Repository\Events
 * @author Roberto Arruda <robertoadearruda@gmail.com>
 */
class RepositoryEntityForceDeleting extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "forceDeleting";
}
