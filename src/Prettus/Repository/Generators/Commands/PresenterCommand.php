<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Prettus\Repository\Generators\PresenterGenerator;
use Prettus\Repository\Generators\TransformerGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        (new PresenterGenerator([
            'name' => $this->argument('name')
        ]))->run();
        $this->info("Presenter created successfully.");


        if (!\File::exists(app_path() . '/Transformers/' . $this->argument('name') . 'Transformer.php')) {
            if ($this->confirm('Would you like to create a Transformer? [y|N]')) {
                (new TransformerGenerator([
                    'name' => $this->argument('name')
                ]))->run();
                $this->info("Transformer created successfully.");
            }
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
            ['name', InputArgument::REQUIRED, 'The name of model for which the presenter is being generated.', null],
        ];
    }
}
