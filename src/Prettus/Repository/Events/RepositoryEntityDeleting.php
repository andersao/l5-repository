<?php
namespace Prettus\Repository\Events;

/**
 * Class RepositoryEntityDeleted
 * @package Prettus\Repository\Events
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RepositoryEntityDeleting extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "deleting";
}
