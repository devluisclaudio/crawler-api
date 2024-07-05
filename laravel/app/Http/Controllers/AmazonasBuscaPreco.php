<?php

namespace App\Http\Controllers;

use App\Repositories\BuscaPrecoRepository;
use Exception;
use Illuminate\Http\Request;

class AmazonasBuscaPreco extends Controller
{
    protected $repository;

    public function __construct(BuscaPrecoRepository $buscaPrecoRepository)
    {
        $this->repository = $buscaPrecoRepository;
    }


    public function index(Request $request)
    {

        try {
            $page = 1;
            $search = '';

            if (isset($request->page) && !empty($request->page))
                $page = $request->page;

            if (isset($request->search) && !empty($request->search))
                $search = $request->search;

            if (empty($search))
                return response()->json([
                    'error' => true,
                    'message' => 'Parametro search=? não pode ser vazio'
                ], 400);

            $html = $this->repository->getBuscaPage($page, $search);


            if ($html) {
                $nodeValues = $this->repository->getScrapingAmazonasAndLatLong($html);
                $postos = $this->repository->getArrayPostos($nodeValues);
                $pagination = $this->repository->getPaginationScrapingAmazonas($html);
            }

            return response()->json([
                'data' => $postos,
                'totalDePaginas' => $pagination
            ]);
        } catch (Exception $ex) {
            report($ex);
            return response()->json($ex->getMessage(), 500);
        }
    }
}
