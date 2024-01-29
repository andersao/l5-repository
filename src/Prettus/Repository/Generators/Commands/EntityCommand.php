<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class EntityCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
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

        if ($this->confirm('Would you like to create a Presenter? [y|N]')) {
            $this->call('make:presenter', [
                'name'    => $this->argument('name'),
                'module'  => $this->argument('module'),
                '--force' => $this->option('force'),
            ]);
        }

        $validator = $this->option('validator');
        if (is_null($validator) && $this->confirm('Would you like to create a Validator? [y|N]')) {
            $validator = 'yes';
        }

        if ($validator == 'yes') {
            $this->call('make:validator', [
                'name'    => $this->argument('name'),
                'module'  => $this->argument('module'),
                '--rules' => $this->option('rules'),
                '--force' => $this->option('force'),
            ]);
        }

        if ($this->confirm('Would you like to create a Controller? [y|N]')) {
            $controller_command = ((float) app()->version() >= 5.5  ? 'make:rest-controller' : 'make:resource');
            $this->call($controller_command, [
                'name'    => $this->argument('name'),
                'module'  => $this->argument('module'),
            ]);
        }

        $this->call('make:repository', [
            'name'        => $this->argument('name'),
            'module'      => $this->argument('module'),
            '--fillable'  => $this->option('fillable'),
            '--rules'     => $this->option('rules'),
            '--validator' => $validator,
            '--force'     => $this->option('force')
        ]);

        $this->call('make:bindings', [
            'name'    => $this->argument('name'),
            'module'  => $this->argument('module'),
            '--force' => $this->option('force')
        ]);
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
                'The name of class being generated.',
                null
            ],
            [
                'module',
                InputArgument::OPTIONAL,
                'The module name for kind of the modular project and creating files on each module',
                null
            ]
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
                'fillable',
                null,
                InputOption::VALUE_OPTIONAL,
                'The fillable attributes.',
                null
            ],
            [
                'rules',
                null,
                InputOption::VALUE_OPTIONAL,
                'The rules of validation attributes.',
                null
            ],
            [
                'validator',
                null,
                InputOption::VALUE_OPTIONAL,
                'Adds validator reference to the repository.',
                null
            ],
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the creation if file already exists.',
                null
            ]
        ];
    }
}
