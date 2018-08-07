<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\Cart;

$app->get("/order/:idorder/clear", function($idorder){
    Cart::clearCart();
    goURL("/order/{$idorder}");
});

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
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja " . SITE_TITLE;
    $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: " . SITE_EMAIL;
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema " . SITE_TITLE . " - " . SITE_EMAIL;

    // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";		
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";


    // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


    // DADOS DA SUA CONTA - ITAÚ
    $dadosboleto["agencia"] = BANCO_AGENCIA; // Num da agencia, sem digito
    $dadosboleto["conta"] = BANCO_CONTA;	// Num da conta, sem digito
    $dadosboleto["conta_dv"] = BANCO_DIGITO; 	// Digito do Num da conta

    // DADOS PERSONALIZADOS - ITAÚ
    $dadosboleto["carteira"] = BANCO_CARTEIRA;  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

    // SEUS DADOS
    $dadosboleto["identificacao"] = SITE_TITLE;
    $dadosboleto["cpf_cnpj"] = SITE_CPF_CNPJ;
    $dadosboleto["endereco"] = SITE_ENDERECO;
    $dadosboleto["cidade_uf"] = SITE_CIDADE_UF;
    $dadosboleto["cedente"] = strtoupper(SITE_TITLE);

    // NÃO ALTERAR!
    $path = PATH_ROOT . DS . 'res' . DS . 'boletophp' . DS .'include' . DS;
    require_once "{$path}funcoes_itau.php"; 
    require_once "{$path}layout_itau.php";
});