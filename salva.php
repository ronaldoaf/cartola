<?php


$mercado=json_decode( file_get_contents("https://api.cartolafc.globo.com/atletas/mercado") );

$jogadores=$mercado->atletas;

$data=json_decode( file_get_contents('data.json') );

$data->{ (string) $jogadores[0]->rodada_id  }=$jogadores;

file_put_contents('data.json', json_encode($data) );