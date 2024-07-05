<?php

namespace App\Repositories;

use DateTime;
use Exception;
use Geohash\GeoHash;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class MenorPrecoRepository
{
    protected string $baseUlr = 'https://menorpreco.notaparana.pr.gov.br/api/v1/produtos';
    protected array $query;
    protected string $url;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    protected function setQuery(array $array): void
    {
        $this->query = $array;
    }

    protected function addUrl(string $complemetUrl): void
    {
        $this->url = $this->baseUlr . $complemetUrl;
    }


    public function getBuscaPage(int $type): string|false
    {
        try {

            $this->addUrl('');

            $this->setQuery([
                "local" => '6gkzqdzcxgzu',
                "tp_comb" => $type,
                "raio" => 20,
                "data" => -1,
                "ordem" => 0
            ]);

            $cliente = new Client();

            $promise = $cliente->getAsync(
                $this->url,
                [
                    'query' => $this->query
                ]
            );

            $response = $promise->wait();

            if ($response->getStatusCode() !== 200) {
                Log::error('Falha no codigo de retorno da pagina ao acessar site! StatusCode: ' . $response->getStatusCode());
                return false;
            }

            return $response->getBody();
        } catch (GuzzleException $ex) {
            report($ex);
            return false;
        }
    }

    public function tratarGeoHash($postos)
    {
        foreach($postos as $posto){
            $u = new GeoHash;
            list($lat, $lng) = $u->decode($posto->local);
            $posto->latitude = $lat;
            $posto->longitude = $lng;
        }

        return $postos;
        
    }
}
