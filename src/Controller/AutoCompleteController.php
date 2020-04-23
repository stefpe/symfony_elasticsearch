<?php

namespace App\Controller;

use Elasticsearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AutoCompleteController extends AbstractController
{
    /**
     * @Route("/ac", name="auto_complete")
     */
    public function index()
    {
        return $this->render('auto_complete/index.html.twig', [
            'controller_name' => 'AutoCompleteController',
        ]);
    }

    /**
     * @Route("/ac/search", name="auto_complete_search", methods="GET")
     */
    public function search(Client $client, array $indexDefinition, Request $request)
    {
        $query = $request->query->get('q');

        $result = $client->search(
            array_merge(
                $indexDefinition,
                ['body' => [
                    'query' => [
                        'match' => [
                            'title' => [
                                'query' => $query,
                                "operator" => "and",
                                "fuzziness" => 2,
                                "analyzer" => "standard"
                            ]
                        ],
                    ],
                    'size' => 3
                ]]
            ));

        $data = array_map(function ($item) {
            return ['value' => $item['_source']['title']];
        }, $result['hits']['hits']);

        return $this->json([
            'products' => $data
        ]);
    }
}
