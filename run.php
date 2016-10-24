<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/vendor/autoload.php';

use Neural\Perceptron;
use Neural\Data;

//PRE-PROCESSAMENTO
$data = new Data();
$dados = $data->importData('mnist_train.csv');
$totalLinhas = count($dados);
if($totalLinhas <= 0) die("\n Erro na importação\n");

//contagem de colunas
$try = explode(",",$dados[0]);
unset($try[0]);
$try = array_values($try);
$totalColunas = count($try);
//fim contagem colunas


//criando neuronios
$perceptrons = array();
for($i=0; $i < 10; $i++){
	$perceptrons[] = new Perceptron($totalColunas, $i);
}

function console(){
	
	echo "\n __________________________________________________________________";
	echo "\n|                                                                  |";
	echo "\n| PHPerceptron - Reconhecimento de imagens em manuscrito           |";
	echo "\n| Utiliza como base de treinamento arquivo MNIST com 60 mil linhas |";
	echo "\n| Utiliza como base de testes arquivo com 10 mil linhas            |";
	echo "\n| Dados estão normalizados, help para mais informações             |";
	echo "\n| **************************************************************** |";
	echo "\n|                                                                  |";
	echo "\n| exit - Sair                                                      |";
	echo "\n| train - Iniciar Treinamento                                      |";
	echo "\n| test - Testar Perceptrons                                        |";
	echo "\n| help - Informações                                               |";
	echo "\n|__________________________________________________________________|\n";
	$line = readline("@phperceptron$> ");
    readline_add_history($line);
	return trim($line);
}


function help(){

	echo "\n *** PHPerceptron *** ";
	echo "\n Ferramenta que (tenta) gerar/treinar uma rede neural para o";
	echo "\n problema de reconhecimento de digitos manuscritos - MNIST";
	echo "\n Consiste de";
	echo "\n - 10 Perceptrons, 0 - 9, especializados em cada dígito";
	echo "\n - Cada perceptron possui 784 entradas, cada um representa um pixel";
	echo "\n - Cada valor que designa a imagem é considerado como 1 ou 0 dependendo do neurônio. \n";
	echo "  Exemplo: se neurônio  é 5 e a imagem possui a coluna inicial como 5 então o valor desta \n coluna é 1, do contrário é 0";
	echo "\n - Cada entrada é normalizada, se pixel for diferente de 0, é 1";
	echo "\n - Pesos com valores randômicos são gerados no inicio, na ordem de\n  0.0 até 0.2 com até 18 casas decimais";
	echo "\n - Após o treinamento, os pesos são exportados para arquivos JSON";
	echo "\n - Funções degrau e sigmoid estão implementadas mas não são utilizadas";
	echo "\n - A função sigmoid é utilizada para os testes";
	echo "\n ******";

}


function treinamento($perceptrons, $dados, $totalColunas, $linhasTreinamento, $epocas){

	$start_date = new DateTime(date('Y-m-d H:i:s'));

	echo "\n Total de imagens maximas para treinamento: $linhasTreinamento \n";
	$data = new Data();

	//INICIO PROCESSAMENTO	

	//treinando
	$epochs = 0;
	while($epochs < $epocas){

		echo "\n Treinamento na Epoca ".($epochs + 1)."\n";
		$epochs++;
		foreach ($perceptrons as $perceptron) {

			echo ' #'.$perceptron->getLabel();
				
			$teste = 0;
			foreach ($dados as $dadosIndice => $dadosLinha) {
							
				$numeroAlvo = substr($dadosLinha, 0,1);
				//echo "\nAlvo sem normalizacao = $numeroAlvo \n";			
				$inputs = explode(",", $dadosLinha);
				unset($inputs[0]);
				$inputs = array_values($inputs);
				$target = ($numeroAlvo == $perceptron->getLabel())? 1 : 0;	//1 se target eh do neuronio respectivo
				$inputs = $data->normalizeInput($inputs);
				$perceptron->train($inputs, $target);
				$teste++;
				if($teste == $linhasTreinamento){ //testando com as primeiras x linhas
					break;
				}
			}

		}

	}

	//TAXA DE SUCESSO
	// echo "\n Taxas de sucesso: \n";
	// foreach ($perceptrons as $perceptron) {
	// 	echo "Perceptron #".$perceptron->getLabel()." = ".($perceptron->getErrorSum() / ($linhasTreinamento * 100))."% \n";
	// }	

	$since_start = $start_date->diff(new DateTime(date('Y-m-d H:i:s')));
	$minutes = $since_start->days * 24 * 60;
	$minutes += $since_start->h * 60;
	$minutes += $since_start->i;
	echo "\n Tempo total de execução = $minutes minutos\n";

	//exportando pesos
	$data = new Data();
	for($i=0; $i < 10; $i++){
		$pesos = $perceptrons[$i]->getWeightVector();
		unset($pesos[$totalColunas]);
		$pesos = array_values($pesos);
		$data->exportArrayJson($pesos, 'perceptron'.$i);
	}

}



function testando($perceptrons, $dados, $totalColunas, $maximoImagens){


	//backup primeiras linhas para teste:
	$linha1 = explode(",", $dados[0]); //imagem de 5
	unset($linha1[0]);
	// $linha2 = explode(",", $dados[1]); //imagem de 0
	// unset($linha2[0]);
	$linha3 = explode(",", $dados[2]); //imagem de 4
	unset($linha3[0]);	

	$data = new Data();
	for($i=0; $i < 10; $i++){	
		$pesos = $data->importArrayJson('perceptron'.$i.'.json');			
		$perceptrons[$i]->setWeightVector($pesos);
	}

	echo "\n Exemplo de testes com 2 imagens \n";
	//TESTANDO
	$maior = -9999.9999;
	$labelMaior = null;
	echo "\n\n Se imagem for de um 5:";
	foreach ($perceptrons as $perceptron) {
		$inputs = $data->normalizeInput($linha1);		
		$teste = $perceptron->test($inputs);
		echo "\n Perceptron #".$perceptron->getLabel()." = ".$teste;	
		
		if($teste > $maior){
			$maior = $teste;
			$labelMaior = $perceptron->getLabel();
		}
		
	}
	echo "\n Mais ativo foi o Perceptron #".$labelMaior;
	echo "\n\n Se imagem for de um 4:";
	$maior = -9999.9999;
	$labelMaior = null;
	foreach ($perceptrons as $perceptron) {
		$inputs = $data->normalizeInput($linha1);
		$teste = $perceptron->test($inputs);
		echo "\n Perceptron #".$perceptron->getLabel()." = ".$teste;
		
		if($teste > $maior){
			$maior = $teste;
			$labelMaior = $perceptron->getLabel();
		}
	}
	echo "\n Mais ativo foi o Perceptron #".$labelMaior;	
	echo "\n Fim de exemplos de testes, testes reais a seguir, pressione enter para continuar: \n";
	$press = readline();

	$dados = $data->importData('mnist_test.csv');
	$totalLinhas = count($dados);
	if($totalLinhas <= 0) die("\n Erro na importação\n");


	$contagens = array();
	for($i=0; $i < 10; $i++){
		$contagens[$i]['acertos'] = 0;
		$contagens[$i]['erros'] = 0;
	}	

	//contagem de colunas
	$try = explode(",",$dados[0]);
	unset($try[0]);
	$try = array_values($try);
	$totalColunas = count($try);
	//fim contagem colunas

	$totalImagens = count($dados);
	if($maximoImagens == 0) $maximoImagens = $totalImagens;
	if( ($maximoImagens != 0) && ($maximoImagens > $totalImagens) ) $maximoImagens = $totalImagens;		
	$maximoIteracoes = 0;	

	echo "\nProcessando testes com ".$maximoImagens." imagens...\n";
	foreach ($dados as $dadosIndice => $dadosLinha) {

		$numeroAlvo = substr($dadosLinha, 0,1);
		//echo "\nAlvo teste = $numeroAlvo \n";	
		$inputs = explode(",", $dadosLinha);
		unset($inputs[0]);
		$inputs = array_values($inputs);	
		$inputs = $data->normalizeInput($inputs);	
		$maior = -9999.9999;
		$labelMaior = null;
		foreach ($perceptrons as $perceptron) {
			$teste = $perceptron->test($inputs);			
			
			if($teste > $maior){
				$maior = $teste;
				$labelMaior = $perceptron->getLabel();
			}
		}

		if($numeroAlvo == $labelMaior) $contagens[$numeroAlvo]['acertos'] += 1;
		if($numeroAlvo != $labelMaior) $contagens[$numeroAlvo]['erros'] += 1;

		$maximoIteracoes++;
		if($maximoIteracoes == $maximoImagens) break;
		
	}

	$acertosGlobal = 0;
	$errosGlobal = 0;
	$tentativasGlobal = 0;
	for($i=0; $i < 10; $i++){
		echo "\n Para o numero $i: \n";
		echo "Acertos = ".$contagens[$i]['acertos']."  | Erros = ".$contagens[$i]['erros'];
		$somaDivisor = $contagens[$i]['acertos'] + $contagens[$i]['erros'];
		if($somaDivisor == 0) $taxa = 0;
		if($somaDivisor != 0) $taxa = ($contagens[$i]['acertos'] * 100) / $somaDivisor;
		echo "\nAcertou %".round($taxa,2)." das vezes\n";
		$acertosGlobal += $contagens[$i]['acertos'];
		$errosGlobal += $contagens[$i]['erros'];
		$tentativasGlobal += $acertosGlobal + $errosGlobal;
	}


	echo "\nAcertos global = ".round((($acertosGlobal / $maximoImagens)*100),2)."%\n";
	echo "Erros global = ".round((($errosGlobal / $maximoImagens)*100),2)."%\n";

	echo "\nFim dos testes!\n";
	exit;
}



$exit  = false;
while (!$exit)
{
	$input = console();

	if ($input == 'exit')
	{
		exit("\nMatando perceptrons... Não se preocupe, dados de treinamento estão exportados em /public!\n");
	}

	if ($input == 'help')
	{
		help();
	}

	if ($input == 'train')
	{		
		echo "\n Total de imagens do arquivo: $totalLinhas";
		echo "\n Informe número máximo de imagens a serem utilizadas no treinamento \n";
		$linhasTreinamento = trim(readline("@phperceptron$> "));
		echo "\n Informe número máximo de épocas para o treinamento \n";
		$epocas = trim(readline("@phperceptron$> "));
		treinamento($perceptrons, $dados, $totalColunas, $linhasTreinamento, $epocas);
	}

	if ($input == 'test')
	{
		echo "\n O sistema já possui um arquivo de testes com centenas de imagens";
		echo "\n Se desejar limitar os testes, informe um valor diferente de 0 \n";
		$maximoImagens = trim(readline("@phperceptron$> "));		
		testando($perceptrons, $dados, $totalColunas, $maximoImagens);
	}
}