<?php namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Prettus\Repository\Generators\ModelGenerator;
use Prettus\Repository\Generators\RepositoryEloquentGenerator;
use Prettus\Repository\Generators\RepositoryInterfaceGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RepositoryCommand extends Command {

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'repository:generate';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new repository.';


    /**
     * @var Collection
     */
    protected $generators  = null;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        $this->generators = new Collection();

        $this->generators->push(new RepositoryInterfaceGenerator([
            'name' => $this->argument('name')
        ]));

        $this->generators->push(new RepositoryEloquentGenerator([
            'name' => $this->argument('name')
        ]));

        $this->generators->push(new ModelGenerator([
            'name'      => $this->argument('name'),
            'fillable'  => $this->option('fillable'),
            'force'     => $this->option('force')
        ]));

        foreach( $this->generators as $generator)
        {
            $generator->run();
        }

        $this->info("Repository created successfully.");
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
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
        ];
    }
}