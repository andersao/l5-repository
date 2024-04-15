<?php

namespace Prettus\Repository\Listeners;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Events\RepositoryEventBase;

/**
 * Class CleanCacheRepository
 *
 * @package Prettus\Repository\Listeners
 * @author  Anderson Andrade <contato@andersonandra.de>
 */
class CleanCacheRepository
{

    /**
     * @var CacheRepository
     */
    protected $cache = null;

    /**
     * @var string|null
     */
    protected ?string $repositoryClass = null;

    /**
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * @var string|null
     */
    protected ?string $action = null;

    /**
     *
     */
    public function __construct()
    {
        $this->cache = app(config('repository.cache.repository', 'cache'));
    }

    /**
     * @param RepositoryEventBase $event
     */
    public function handle(RepositoryEventBase $event)
    {
        try {
            $cleanEnabled = config("repository.cache.clean.enabled", true);

            if ($cleanEnabled) {
                $this->repositoryClass = $event->getRepositoryClass();
                $this->model           = $event->getModel();
                $this->action          = $event->getAction();

                if (config("repository.cache.clean.on.{$this->action}", true)) {
                    $this->cache->tags([$this->repositoryClass])->flush();
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
