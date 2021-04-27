<?php

namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityRestored
 * @package Prettus\Repository\Events
 * @author Roberto Arruda <robertoadearruda@gmail.com>
 */
class RepositoryEntityRestored extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "restored";
}
