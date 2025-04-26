<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomValidationException;
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

    public function simular(Request $dados)
    {
        $validator = \Validator::make($dados->all(), [
            'valor_emprestimo' => 'required|numeric|min:0',
            'instituicoes' => 'required|array|min:1',
            'instituicoes.*' => 'string',
            'convenios' => 'required|array|min:1',
            'convenios.*' => 'string',
            'parcela' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new CustomValidationException($validator->errors());
        }

        $valorEmprestimo = $dados->input('valor_emprestimo');
        $instituicoesSelecionadas = $dados->input('instituicoes');
        $conveniosSelecionados = $dados->input('convenios');
        // $parcelaSelecionada = $dados->input('parcela');

        $instituicoes = json_decode(file_get_contents(storage_path('app/baseJson/instituicoes.json')), true);
        $taxas = json_decode(file_get_contents(storage_path('app/baseJson/taxas_instituicoes.json')), true);

        $resultado = [];

        foreach ($instituicoes as $instituicao) {

            if (!in_array($instituicao['chave'], $instituicoesSelecionadas)) {
                continue;
            }

            $taxasInstituicao = array_filter($taxas, function ($taxa) use ($instituicao, $conveniosSelecionados) {
                return $taxa['instituicao'] === $instituicao['chave']
                    && in_array($taxa['convenio'], $conveniosSelecionados);


                // $taxasInstituicao = array_filter($taxas, function($taxa) use ($instituicao, $conveniosSelecionados, $parcelaSelecionada) {
                //     return $taxa['instituicao'] === $instituicao['chave']
                //         && in_array($taxa['convenio'], $conveniosSelecionados)
                //         && $taxa['parcelas'] == $parcelaSelecionada;
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
