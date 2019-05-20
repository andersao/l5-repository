<?php
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityCreated
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RepositoryEntityCreated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "created";
}
