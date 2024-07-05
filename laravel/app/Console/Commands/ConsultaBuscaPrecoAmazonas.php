<?php

namespace App\Console\Commands;

use App\Repositories\BuscaPrecoRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConsultaBuscaPrecoAmazonas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:consulta-busca-preco-amazonas';

    protected $repository;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Essa rotina faz um scraping do busca preço do site da sefaz no amazonas';

    public function __construct(BuscaPrecoRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        if (Cache::has('html'))
            $html = Cache::get('html');
        else
            $html = $this->repository->getBuscaPage(1, 'gasolina comum');

        Cache::set('html', $html);

        // if ($html) {
        //     $nodeValues = $this->repository->getScrapingAmazonasAndLatLong($html);
        //     $postos = $this->repository->getArrayPostos($nodeValues);
        //     $pagination = $this->repository->getPaginationScrapingAmazonas($html);
        // }

        
    }
}
