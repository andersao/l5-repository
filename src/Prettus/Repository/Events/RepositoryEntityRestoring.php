<?php

namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityRestoring
 * @package Prettus\Repository\Events
 * @author Roberto Arruda <robertoadearruda@gmail.com>
 */
class RepositoryEntityRestoring extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "restoring";
}
