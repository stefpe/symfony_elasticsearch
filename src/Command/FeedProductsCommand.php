<?php

namespace App\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class FeedProductsCommand
 * @package App\Command
 */
class FeedProductsCommand extends Command
{
    protected static $defaultName = 'app:feed_products';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $productFilePath;

    /**
     * @var array
     */
    private $indexDefinition;

    /**
     * FeedProductsCommand constructor.
     * @param Client $client
     * @param string $productFilePath
     * @param array $indexDefinition
     */
    public function __construct(Client $client, string $productFilePath, array $indexDefinition)
    {
        $this->client = $client;
        $this->productFilePath = $productFilePath;
        $this->indexDefinition = $indexDefinition;
        parent::__construct(null);
    }

    /**
     * Command configuration setup.
     */
    protected function configure()
    {
        $this
            ->setDescription('Feed products to Elasticsearch');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('CREATING INDEX....');
        $this->createIndex();

        $io->note('FEEDING INDEX....');
        $this->feedData();

        $io->success('FEEDING DONE');

        return 0;
    }

    /**
     * Parse csv and feed the data to Elasticsearch.
     */
    private function feedData(): void
    {
        $productFile = new \SplFileObject($this->productFilePath);
        $productFile->fgetcsv();//ignore headline

        while ($data = $productFile->fgetcsv()) {
            list($rowIdx, $title, $price, $retailer, $rating, $desc) = $data;
            $doc = array_merge(
                $this->indexDefinition,
                [
                    'id' => $rowIdx,
                    'body' => [
                        'title' => $title,
                        'price' => (float)$price,
                        'retailer' => $retailer,
                        'rating' => (float)$rating,
                        'desc' => $desc
                    ]
                ]
            );
            $this->client->index($doc);
        }
    }

    /**
     * Creates index with mapping and analyzer.
     */
    private function createIndex(): void
    {
        if ($this->client->indices()->exists($this->indexDefinition)){
            $this->client->indices()->delete($this->indexDefinition);
        }

        $this->client->indices()->create(
            array_merge(
                $this->indexDefinition,
                [
                    'body' => [
                        'settings' => [
                            'number_of_shards' => 1,
                            'number_of_replicas' => 0,
                            "analysis" => [
                                "analyzer" => [
                                    "autocomplete" => [
                                        "tokenizer" => "autocomplete",
                                        "filter" => ["lowercase"]
                                    ]
                                ],
                                "tokenizer" => [
                                    "autocomplete" => [
                                        "type" => "edge_ngram",
                                        "min_gram" => 2,
                                        "max_gram" => 20,
                                        "token_chars" => [
                                            "letter",
                                            "digit"
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "mappings" => [
                            "properties" => [
                                "title" => [
                                    "type" => "text",
                                    "analyzer" => "autocomplete",
                                    "search_analyzer" => "standard"
                                ]
                            ]
                        ]
                    ]
                ]
            )
        );
    }
}
