<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EntityCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:entity';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new entity.';

    /**
     * @var Collection
     */
    protected $generators = null;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        $this->call('make:repository', [
            'name'       => $this->argument('name'),
            '--fillable' => $this->option('fillable'),
            '--rules'    => $this->option('rules'),
            '--force'    => $this->option('force')
        ]);

        if ($this->confirm('Would you like to create a Presenter? [y|N]')) {
            $this->call('make:presenter', [
                'name' => $this->argument('name')
            ]);
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
            ['name', InputArgument::REQUIRED, 'The name of class being generated.', null],
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
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['rules', null, InputOption::VALUE_OPTIONAL, 'The rules of validation attributes.', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null]
        ];
    }
}