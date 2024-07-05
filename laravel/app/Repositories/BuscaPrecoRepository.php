<?php

namespace App\Repositories;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class BuscaPrecoRepository
{
    protected string $baseUlr = 'https://buscapreco.sefaz.am.gov.br/item/grupo/page/';
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

    protected function addPage(int $page): void
    {
        $this->url = $this->baseUlr . $page;
    }

    public function getBuscaPage(int $page, string $search): string|false
    {
        try {
            $this->addPage($page);

            $this->setQuery([
                "consultaExata" => 0,
                "descricaoProd" => $search,
                "tipoConsulta" => 24,
                "distancia" => "99999",
                "municipio" => "Manaus"
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

    public function getById($id)
    {
        return;
    }

    public function getLatLong(Crawler $node): string | false
    {
        try {
            $a = $node->filter('div.col.s4.left-align > a.modal-trigger.tooltipped')->attr('onclick');
            if (!empty($a))
                return $a;

            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function tratarDados(string $stringNode): array
    {
        $segmento1 = explode("R$", $stringNode);
        $segmento2 = explode("Há", $segmento1[1]);
        $segmento3 = explode("(s)", $segmento2[1]);
        $string = $segmento3[0] . $segmento3[1] . $segmento3[2];

        $timestamp = $this->ajustaTimestamp($string);

        [$nomeEmpresa, $restodaString] = explode(",", $segmento3[3], 2);

        if (str_contains($restodaString, 'lat#')) {
            [$endereco, $latLong] = explode("lat#", $restodaString);
            preg_match("/-?\d+\.\d+,\s-?\d+\.\d+/", $latLong, $matches);
            $latLong = $matches[0] ?? '';

            return [trim($segmento1[0]), trim($segmento2[0]), $timestamp, trim($nomeEmpresa), trim($endereco), trim($latLong)];
        }

        $endereco = $restodaString;
        return [trim($segmento1[0]), trim($segmento2[0]), $timestamp, trim($nomeEmpresa), trim($endereco), ''];
    }

    protected function ajustaTimestamp(string $string): string
    {
        try {
            $pattern = '/(\d+)\s+hora\s+(\d+)\s+minuto\s+(\d+)\s+segundo/';
            preg_match($pattern, $string, $matches);

            $hours = $matches[1];
            $minutes = $matches[2];
            $seconds = $matches[3];

            $datetime = new DateTime();
            $datetime->setTime($hours, $minutes, $seconds);

            $timestamp = $datetime->format('Y-m-d H:i:s.u');

            return $timestamp;
        } catch (Exception $ex) {
            report($ex);
            return 'Não definido';
        }
    }

    public function getScrapingAmazonasAndLatLong(string $html): array
    {
        $crawler = new Crawler($html);

        $nodeValues = $crawler->filter('div.col.s12.m4')->each(function (Crawler $node, $i): string {

            $lat = $this->getLatLong($node);
            $htmlResult = $node->text();

            $htmlResult .= $lat ? 'lat#' . $lat : '';

            return  $htmlResult;
        });

        return $nodeValues;
    }

    public function getArrayPostos(array $nodeValues): array
    {
        $postos = [];
        foreach ($nodeValues as $node) {
            [$produto, $preco, $tempo, $nomeEmpresa, $endereco, $latLong] = $this->tratarDados($node);

            $postos[] = [
                'produto' => $produto,
                'preco' => $preco,
                'tempo' => $tempo,
                'nomeEmpresa' => $nomeEmpresa,
                'endereco' => $endereco,
                'latLong' => $latLong
            ];
        }

        return $postos;
    }

    public function getPaginationScrapingAmazonas(string $html): int
    {
        $crawler = new Crawler($html, $this->baseUlr);

        $nodeValues = $crawler->filter('ul.pagination > li > a')->links();

        $ultimaposicao = count($nodeValues) - 1;
        $ultimaPosiçãoDePaginas = $nodeValues[$ultimaposicao];
        $totalDePaginas =  str_replace($this->baseUlr, '', $ultimaPosiçãoDePaginas->getUri());

        return (int) $totalDePaginas;
    }
}
