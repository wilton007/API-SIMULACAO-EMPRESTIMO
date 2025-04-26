<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulacaoController extends Controller
{
    public function getInstituicoes()
{
     $instituicoes = json_decode(file_get_contents(storage_path('app/baseJson/instituicoes.json')), true);
     return response()->json($instituicoes);

}

public function getConvenios()
{
    $convenios = json_decode(file_get_contents(storage_path('app/baseJson/convenios.json')), true);
    return response()->json($convenios);
}

public function simular(Request $request)
{
    $valorEmprestimo = $request->input('valor_emprestimo');
    $instituicoes = json_decode(file_get_contents(storage_path('app/baseJson/instituicoes.json')), true);
    $convenios = json_decode(file_get_contents(storage_path('app/baseJson/convenios.json')), true);
    $taxas = json_decode(file_get_contents(storage_path('app/baseJson/taxas_instituicoes.json')), true);

    $resultado = [];

    
    foreach ($instituicoes as $instituicao) {
        $taxasInstituicao = array_filter($taxas, function($taxa) use ($instituicao) {
            return $taxa['instituicao'] === $instituicao['chave'];
        });

        foreach ($taxasInstituicao as $taxa) {

            $valorParcela = $valorEmprestimo * $taxa['coeficiente'];


            $resultado[$instituicao['chave']][] = [
                'taxa_juros' => $taxa['taxaJuros'],
                'parcelas' => $taxa['parcelas'],
                'valor_parcela' => round($valorParcela, 2),
                'convenio' => $taxa['convenio']
            ];
        }
    }

    return response()->json($resultado);



}
}
