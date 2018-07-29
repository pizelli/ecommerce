<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;

$app->get("/order/:idorder", function($idorder){
    User::verifyLogin(false);
    $order = new Order;
    $order->get((int)$idorder);
    $page = new Page();
    $page->setTpl("payment", [
        'order' => $order->getValues()
    ]);
});

$app->get('/boleto/:idorder', function($idorder){
    User::verifyLogin(false);
    $order = new Order;
    $order->get((int)$idorder);

    // DADOS DO BOLETO PARA O SEU CLIENTE
    $dias_de_prazo_para_pagamento = 10;
    $taxa_boleto = 5.00;
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
    $valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace(",", ".",$valor_cobrado);
    $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

    $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
    $dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
    $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
    $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
    $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
    $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

    // DADOS DO SEU CLIENTE
    $dadosboleto["sacado"] = utf8_encode($order->getdesperson());
    $dadosboleto["endereco1"] = utf8_encode($order->getdesaddress());
    $dadosboleto["endereco2"] = utf8_encode("{$order->getdescity()} - {$order->getdesstate()} -  CEP: {$order->getdeszipcode()}");

    // INFORMACOES PARA O CLIENTE
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hortfruit Digital E-commerce";
    $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@loja.iexchange.com.br";
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hortfruit Digital E-commerce - loja.iexchange.com.br";

    // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";		
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";


    // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


    // DADOS DA SUA CONTA - ITAÚ
    $dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
    $dadosboleto["conta"] = "48781";	// Num da conta, sem digito
    $dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

    // DADOS PERSONALIZADOS - ITAÚ
    $dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

    // SEUS DADOS
    $dadosboleto["identificacao"] = "Hortfruit Digital E-commerce";
    $dadosboleto["cpf_cnpj"] = "00.000.000/0000-00";
    $dadosboleto["endereco"] = "Rua não sei onde fica, 666 - Indefinido, 66666-666";
    $dadosboleto["cidade_uf"] = "Inferno de Dante - RJ";
    $dadosboleto["cedente"] = strtoupper("Hortfruit Digital E-commerce");

    // NÃO ALTERAR!
    $path = PATH_ROOT . DS . 'res' . DS . 'boletophp' . DS .'include' . DS;
    require_once "{$path}funcoes_itau.php"; 
    require_once "{$path}layout_itau.php";
});