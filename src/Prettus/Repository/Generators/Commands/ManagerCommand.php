<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Prettus\Repository\Generators\FileAlreadyExistsException;
use Prettus\Repository\Generators\ManagerGenerator;
use Prettus\Repository\Generators\MigrationGenerator;
use Prettus\Repository\Generators\ModelGenerator;
use Prettus\Repository\Generators\RepositoryEloquentGenerator;
use Prettus\Repository\Generators\RepositoryInterfaceGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ManagerCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:manager';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new manager.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Manager';

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
        try {
            (new ManagerGenerator([
                'name' => $this->argument('name'),
                'action' => $this->option('action'),
            ]))->run();
            $this->info("Manager created successfully.");
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
                'The name of class being generated.',
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
                'action',
                'f',
                InputOption::VALUE_REQUIRED,
                'Action already exists.',
                null
            ]
        ];
    }

}
