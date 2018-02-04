<?php
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contact@andersonandra.de>
 */
class RepositoryEntityUpdated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "updated";
}
