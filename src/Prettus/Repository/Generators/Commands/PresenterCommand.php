<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
        $this->generators = new Collection();

        $this->generators->push(new PresenterGenerator([
            'name' => $this->argument('name')
        ]));

        if (!\File::exists(app_path() . '/Transformers/' . $this->argument('name') . 'Transformer.php')) {
            $this->info('It seems that you did not create a Transformer for ' . $this->argument('name'));
            if ($this->confirm('Would you like to create one? [y|N]')) {
                $this->generators->push(new TransformerGenerator([
                    'name' => $this->argument('name')
                ]));
            }
        }

        foreach ($this->generators as $generator) {
            $generator->run();
        }

        $this->info("Presenter created successfully.");
        $this->info("Transformer created successfully.");
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
