<?php

namespace App\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnalyzeAutocompleteCommand extends Command
{
    protected static $defaultName = 'app:analyze_autocomplete';

    private const ARG = 'str';

    /**
     * Client
     */
    private $client;

    /**
     * @var array
     */
    private $indexDefinition;

    /**
     * AnalyzeAutocompleteCommand constructor.
     * @param Client $client
     * @param array $indexDefinition
     */
    public function __construct(Client $client, array $indexDefinition)
    {
        $this->client = $client;
        $this->indexDefinition = $indexDefinition;
        parent::__construct(null);
    }


    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument(self::ARG, InputArgument::REQUIRED, 'Argument description');
        ;
    }

    /**
     * Analyze a given inpput string.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $stringToAnalyze = $input->getArgument(self::ARG);

        $result = $this->client->indices()->analyze(
            array_merge($this->indexDefinition, ['body' => ['analyzer' => 'autocomplete', 'text' => $stringToAnalyze]])
        );

        $tokens = array_reduce($result['tokens'], function($glue, $item){
            return $glue .= $item['token'] . ' - ';
        });
        $tokens = rtrim($tokens,' - ');

        $io->success($tokens);

        return 0;
    }
}
