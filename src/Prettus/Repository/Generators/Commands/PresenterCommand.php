<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\FileAlreadyExistsException;
use Prettus\Repository\Generators\PresenterGenerator;
use Prettus\Repository\Generators\TransformerGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class PresenterCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class PresenterCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:presenter';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new presenter.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Presenter';

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
            (new PresenterGenerator([
                'name'  => $this->argument('name'),
                'force' => $this->option('force'),
            ]))->run();
            $this->info("Presenter created successfully.");

            if (!\File::exists(app()->path() . '/Transformers/' . $this->argument('name') . 'Transformer.php')) {
                if ($this->confirm('Would you like to create a Transformer? [y|N]')) {
                    (new TransformerGenerator([
                        'name'  => $this->argument('name'),
                        'force' => $this->option('force'),
                    ]))->run();
                    $this->info("Transformer created successfully.");
                }
            }
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
                'The name of model for which the presenter is being generated.',
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
            ]
        ];
    }
}
