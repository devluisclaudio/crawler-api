<?php

namespace App\Interfaces;

interface BuscaPrecoRepositoryInterface
{
    public function getBuscaPage($page, $search);
    public function getById($id);
    public function getLatLong($node);
    public function tratarDados($stringNode);
    public function getScrapingAmazonasAndLatLong($html);
    public function getArrayPostos($nodeValues);
    public function getPaginationScrapingAmazonas($html);
}
