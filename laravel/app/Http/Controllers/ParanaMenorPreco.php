<?php

namespace App\Http\Controllers;

use App\Repositories\MenorPrecoRepository;
use Exception;
use Illuminate\Http\Request;

class ParanaMenorPreco extends Controller
{
    protected $repository;

    public function __construct(MenorPrecoRepository $buscaPrecoRepository)
    {
        $this->repository = $buscaPrecoRepository;
    }


    public function index(Request $request)
    {

        try {
            $type = 1;

            if (isset($request->type) && !empty($request->type))
                $type = $request->type;

            if (empty($type))
                return response()->json([
                    'error' => true,
                    'message' => 'Parametro type=? não pode ser vazio'
                ], 400);

            $json = $this->repository->getBuscaPage($type);


            if ($json) {
                $ojbect = json_decode($json);
                $postosDecoderLatLong = $this->repository->tratarGeoHash($ojbect->produtos);
            }

            return response()->json([
                'data' => $postosDecoderLatLong
            ]);
        } catch (Exception $ex) {
            report($ex);
            return response()->json($ex->getMessage(), 500);
        }
    }
}
