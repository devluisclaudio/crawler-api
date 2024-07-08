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


    public function combustivel(Request $request)
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

    public function index(Request $request)
    {

        try {
            if (isset($request->search) && !empty($request->search))
                $search = $request->search;

            if (isset($request->category) && !empty($request->category))
                $category = $request->category;

            if (empty($search))
                return response()->json([
                    'error' => true,
                    'message' => 'Parametro search=? não pode ser vazio'
                ], 400);

            if (empty($category))
                $json = $this->repository->getBuscaCategorias($search);
            else
                $json = $this->repository->getBuscaProdutos($search, $category);


            if ($json) {
                $ojbect = json_decode($json);

                if (!empty($category)) {
                    $postosDecoderLatLong = $this->repository->tratarGeoHash($ojbect->produtos);
                    return response()->json(['data' => $postosDecoderLatLong]);
                }

                return response()->json(['data' => $ojbect]);
            }
            return response()->json(['data' => $json]);
        } catch (Exception $ex) {
            report($ex);
            return response()->json($ex->getMessage(), 500);
        }
    }
}
