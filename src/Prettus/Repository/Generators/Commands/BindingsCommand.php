<?php
namespace Prettus\Repository\Generators\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Prettus\Repository\Generators\BindingsGenerator;
use Prettus\Repository\Generators\FileAlreadyExistsException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class BindingsCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class BindingsCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:bindings';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Add repository bindings to service provider.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Bindings';

    /**
     * Execute the command.
     *
     * @see fire()
     * @return void
     */
    public function handle(){
        $this->laravel->call([$this, 'fire'], func_get_args());
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        try {
            $bindingGenerator = new BindingsGenerator([
                'name' => $this->argument('name'),
                'force' => $this->option('force'),
            ]);
            // generate repository service provider
            if (!file_exists($bindingGenerator->getPath())) {
                $this->call('make:provider', [
                    'name' => $bindingGenerator->getConfigGeneratorClassPath($bindingGenerator->getPathConfigNode()),
                ]);
                // placeholder to mark the place in file where to prepend repository bindings
                $provider = File::get($bindingGenerator->getPath());
                File::put($bindingGenerator->getPath(), vsprintf(str_replace('//', '%s', $provider), [
                    '//',
                    $bindingGenerator->bindPlaceholder
                ]));
            }
            $bindingGenerator->run();
            $this->info($this->type . ' created successfully.');
        } catch (FileAlreadyExistsException $e) {
            $this->error($this->type . ' already exists!');

            return false;
        }
    }


    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of model for which the controller is being generated.',
                null
            ],
        ];
    }


    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the creation if file already exists.',
                null
            ],
        ];
    }
}
