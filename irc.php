<?php

// Time zone setting
date_default_timezone_set('America/Santiago');

// configurações de parametros do bot
$server = 'irc.chknet.cc'; // irc chknet server
$port = 6667; // port irc.chknet.cc
$nickname = 'AmeViadex24'; //nickname of bot viadex24
$ident = 'AmeViadex24'; //indeitify bot 
//$identify = 'viadex088'; //password of profile viadex24
$gecos = '02[BOT] OF #07USACC 10BY NORAH_C_IV'; //profile of viadex24
$channel = '#USACC'; // channel of chknet made with norah

// conexão com a rede
$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
$error = socket_connect( $socket, $server, $port );


// adicione algum tratamento de erro caso a conexão não seja bem-sucedida
if ( $socket === false ) {
    $errorCode = socket_last_error();
    $errorString = socket_strerror( $errorCode );
    die( "Error $errorCode: $errorString\n");
}

//enviando informações de registro
socket_write( $socket, "NICK $nickname\r\n" );
//socket_write( $socket, "PASS $identify\r\n" );
socket_write( $socket, "USER $ident * 8 :$gecos\r\n" );

// Finalmente, Loop Até o Soquete Fecha

while ( is_resource( $socket ) ) {
    
    //buscar os dados do soquete.
    $data = trim( socket_read( $socket, 1024, PHP_NORMAL_READ ) );
    echo $data . "\n";

    // Dividindo os dados em pedaços
    $d = explode(' ', $data);
    
    // Preenchendo o array evita feio indefinido
    $d = array_pad( $d, 10, '' );

    // Manipulador de ping
    // PING : irc.chknet.cc
    if ( $d[0] === 'PING' ) {
      socket_write( $socket, 'PONG ' . $d[1] . "\r\n" );
    }
     if ( $d[1] === '376' || $d[1] === '422' ) {
       socket_write( $socket, 'JOIN ' . $channel . "\r\n" );
       socket_write( $socket, "PRIVMSG NickServ :identify viadex088\n" );
       socket_write( $socket, "PART #BRAZIL,#UNIX,#CCPOWER\n");

     }

     //   [0]                       [1]    [2]     [3]
     //  Nickname!ident@hostname PRIVMSG #USACC : !test
      if ( $d[3] === ':!help' ) {
        $moo = "COMANDOS DA SALA → 07https://pastecode.xyz/view/raw/a99cf202 ";
        socket_write( $socket, 'PRIVMSG ' . $d[2] . " :$moo\r\n" );
     }

     if ( $d[3] === ':!status' ) {
        $moo = "12[$d[2]] → 02CHK 15[OFF] 02BIN 09[ON] 02IP 09[ON]02 CELL 09[ON]02 GG 14[OFF] 03CANAL EM DESENVOLVIMENTO ! ";
        socket_write( $socket, 'PRIVMSG ' . $d[2] . " :$moo\r\n" );
     }

      if ( $d[3] === ':!bin' ) {
          if ($d[3] === ':!bin') {
    // SEPARA SOMENTE OS 6 PRIMEIROS DIGITOS
    $checkBIN = substr($d[4], 0, 6);
    // CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://lookup.binlist.net/'.$checkBIN);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $output = curl_exec($ch);
    curl_close($ch);

    // DECODIFICANDO RESPOSTA EM JSON
    $jsonOUTPUT = json_decode($output, true);

    // DEFININDO VARIAVEL COM NOME AMIGAVEL
    $bandeira = $jsonOUTPUT['scheme'];
    $tipo = $jsonOUTPUT['type'];
    $nivel = $jsonOUTPUT['brand'];
    $pais = $jsonOUTPUT['country']['alpha2'];
    $moeda = $jsonOUTPUT['country']['currency'];
    $bancoNOME = $jsonOUTPUT['bank']['name'];
    $bancoURL = $jsonOUTPUT['bank']['url'];
    $bancoPHONE = $jsonOUTPUT['bank']['phone'];

    // DEFININDO MENSAGEM DE RESPOSTA AO IRC
    $moo = "10[BIN] 03$checkBIN 10[BANDEIRA] 03$bandeira 10[TIPO]03 $tipo 10[NIVEL]03 $nivel 10[MOEDA]03 $moeda 10[PAÍS]03 $pais 10[BANCO]03 $bancoNOME - $bancoURL 10[$bancoPHONE]";

    // ENVIANDO RESPOSTA AO IRC
    socket_write($socket,'PRIVMSG '.$d[2]." :$moo\r\n" );
}

      }

     if ( $d[3] === ':!chk' ) {
        $moo = "  → COMANDO 01[DESATIVADO] 08PELO OWNER ";
        socket_write( $socket, 'PRIVMSG ' . $d[2] . " :$moo\r\n" );
     }
     
     if ( $d[3] === ':!ip' ) {
         // SEPARA SOMENTE OS 11 DIGITOS
    $iplist = $d[4];
    $IPKEY = '7b6fab341bd4c7cd10c7e116c177c8c8fb246f77033f020b37d6b88467f14de1';
    // CURL
    $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, "http://api.ipinfodb.com/v3/ip-city/?key=$IPKEY&ip=$iplist");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    //SEPARANDO DADOS
    $ex = explode(';', $output);
    
    // DEFININDO MENSAGEM DE RESPOSTA AO IRC
    $moo = "08[-GeoIP-] → 10$ex[2] $ex[3] 08[ESTADO-PROVINCIA]10 $ex[5] 08[CIDADE] 10$ex[6] 08[PAIS] 10$ex[4] 08[CEP] 10$ex[7] 08[LO]10$ex[8] 08[LA]10$ex[9] ";

    // ENVIANDO RESPOSTA AO IRC
    print_r('PRIVMSG ');
    socket_write($socket,'PRIVMSG '.$d[2]." :$moo\r\n" );
     }

     if ( $d[3] === ':!cell' ) {
    // SEPARA SOMENTE OS 11 DIGITOS
    $Number = substr($d[4], 0, 16);
    $keyAPI = '5fa2c8a935ac364827f80d450b07d53d';
    // CURL
    $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, "http://apilayer.net/api/validate?access_key=$keyAPI&number=$Number&country_code=&format=1");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $output = curl_exec($ch);
    curl_close($ch);

    // DECODIFICANDO RESPOSTA EM JSON
    $jsonOUTPUT = json_decode($output, true);

    // DEFININDO VARIAVEL COM NOME AMIGAVEL
    $numero = $jsonOUTPUT['international_format'];
    $codpais = $jsonOUTPUT['country_code'];
    $pais = $jsonOUTPUT['country_name'];
    $estado = $jsonOUTPUT['location'];
    $operadora = $jsonOUTPUT['carrier'];
    $linha = $jsonOUTPUT['line_type'];

    // DEFININDO MENSAGEM DE RESPOSTA AO IRC
    $moo = "10[NUMERO] 03$numero 10[LOCAL]03 $codpais 10[PAIS]03 $pais 10[ESTADO]03 $estado 10[OPERADORA]03 $operadora 10[LINHA]03 $linha";

    // ENVIANDO RESPOSTA AO IRC
    socket_write($socket,'PRIVMSG '.$d[2]." :$moo\r\n" );
    
      }
      
      if ( $d[3] === ':!unban' ) {
      $d[1]=['-b',explode(' ',$moo)];
            }


}

?>
