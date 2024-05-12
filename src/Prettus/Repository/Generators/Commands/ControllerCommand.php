<?php

namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Prettus\Repository\Generators\ControllerGenerator;
use Prettus\Repository\Generators\FileAlreadyExistsException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ControllerCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class ControllerCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:resource';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new RESTful controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * ControllerCommand constructor.
     */
    public function __construct()
    {
        $this->name = ((float)app()->version() >= 5.5 ? 'make:rest-controller' : 'make:resource');
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     * @see fire()
     */
    public function handle()
    {
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

            $available_package = config("repository.generator.package");
            $package_commands = config("repository.generator.packageCommands");
            $commands = "make:request";

            $create_arguments = [
                'name' => $this->argument('name') . 'CreateRequest',
            ];
            $update_arguments = [
                'name' => $this->argument('name') . 'UpdateRequest',
            ];

            if (!is_null($available_package) && trim($available_package) != '' &&
                !empty($package_commands) && $this->argument('module') &&
                $package_commands[$available_package]['requests']) {

                $commands = $package_commands[$available_package]['requests'];
                $create_arguments = array_merge($create_arguments,[
                    'module' => $this->argument('module'),
                ]);
                $update_arguments = array_merge($update_arguments,[
                    'module' => $this->argument('module'),
                ]);
            }

            // Generate create request for controller
            $this->call($commands, $create_arguments);
            // Generate update request for controller
            $this->call($commands, $update_arguments);

            (new ControllerGenerator([
                'name' => $this->argument('name'),
                'module' => $this->argument('module'),
                'force' => $this->option('force'),
            ]))->run();

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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the creation if file already exists.',
                null
            ],
        ];
    }
}
