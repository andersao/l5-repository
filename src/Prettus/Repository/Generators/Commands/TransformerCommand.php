<?php
namespace Prettus\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Prettus\Repository\Generators\TransformerGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TransformerCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:transformer';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new transformer.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {
        (new TransformerGenerator([
            'name' => $this->argument('name')
        ]))->run();
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
            ['name', InputArgument::REQUIRED, 'The name of model for which the transformer is being generated.', null],
        ];
    }
}
