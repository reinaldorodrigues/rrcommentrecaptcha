<?php
/*
Plugin Name: RRC Recaptcha
Plugin URI: http://www.reinaldorodrigues.com.br/wordpress/plugin/rrcommentrecaptcha
Description: Plugin do Wordpress para inserir recaptcha no formulario 
Version: 1.0.0
Author: Reinaldo Rodrigues
Author URI:  http://www.reinaldorodrigues.com.br/sobre/
License: GPLv2
*/

/*
 *      Copyright 2014 Reinaldo Rodrigues <contato@reinaldodoridrigues.com.br>
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

define('RR_PATH',dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

// adicionar o plugin no menu
add_action('admin_menu', 'add_admin_menu_rrcomment');

function add_admin_menu_rrcomment(){    
    $icon = plugins_url( 'rrcommentrecaptcha'.DS.'assets'.DS.'images'.DS.'icon.png' );
    add_menu_page( 'RRC Recaptcha','RRC Recaptcha', 'administrator', 'rrcommentrecaptcha','action_rrcrecaptcha', $icon );
}

// ação executada na area administrativa
function action_rrcrecaptcha(){
    
    // recebe os dados do formulario
    $sitekey    = $_POST['sitekey'];
    $secretkey  = $_POST['secretkey'];
    $btnsave    = $_POST['saverrcrecaptcha'];
    
    $error      = FALSE;
    
    // caso seja clicado em salvar
    if($btnsave){
        // faz a verificação de erro
        if($sitekey == "" || $sitekey == NULL){
            
            update_option('rrcrecaptcha_messages', array(
                'tipo'=>'erro',
                'mensagem' => 'O campo Site Key é de preenchimento obrigatório!'
                )
                    
            );
            $error = TRUE;        
        }elseif (preg_match('/[^a-z_\-0-9]/i', $sitekey)){
            
            update_option('rrcrecaptcha_messages', array(
                'tipo'=>'erro',
                'mensagem' => 'O campo Site Key não está correto!'
                )
            );
            $error = TRUE;        
        
        }elseif ($secretkey == "" || $secretkey == NULL) {
            
            update_option('rrcrecaptcha_messages', array(
                'tipo'=>'erro',
                'mensagem' => 'O campo Secret Key é de preenchimento obrigatório!'
                )
            );
            $error = TRUE; 
        
        }elseif (preg_match('/[^a-z_\-0-9]/i', $secretkey)) {
            update_option('rrcrecaptcha_messages', array(
                'tipo'=>'erro',
                'mensagem' => 'O campo Secret Key não está correto!'
                )
            );
            $error = TRUE;
        }
        
        if(!$error){
            
            update_option('rrcrecaptcha_token', array(
                'sitekey'   => $sitekey,
                'secretkey' => $secretkey
                )
            );
            
            update_option('rrcrecaptcha_messages', array(
                'tipo'=>'sucesso',
                'mensagem' => 'Dados salvo com sucesso!'
                )
            );
            
        }
        
    }
    
    // incluir o formulario
    include_once RR_PATH.DS.'rrcrecaptcha_form.php';
}

// altera os campos 
add_filter('comment_form_default_fields', 'alterar_campos');

function alterar_campos($campos) {

    $campos['author']   = '<label>Nome:</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" ' . $aria_req . $html_req . ' />';
    $campos['email']    = '<label>E-mail:</label><input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-describedby="email-notes" ' . $aria_req . $html_req . ' /> ';
    $campos['url']      = ''; // remove o campo site 
    
    return $campos;
}

// ajustando os campos defaults
add_filter('comment_form_defaults', 'ajuste_campos');

function ajuste_campos($campos) {
   
    // altera o textarea
    $campos['comment_field']            = '<label>' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" required="required" ></textarea>'; 
    
    // remove titulo do comenterio
    $campos['title_reply_before']       = null;
    $campos['title_reply_after']        = null;
    $campos['title_reply']              = NULL;
    
    // aleterar texto
    $campos['comment_notes_before']     = '<div class="resen-coment">Todos os campos são de preenchimento obrigatório!</div>';

    // pega o sitekey 
    $option = get_option('rrcrecaptcha_token');
    
    // adiciona um campo (recaptcha
    $campos[ 'fields' ]['recaptcha']    = '<div id="recap"><div class="g-recaptcha" data-sitekey="'.$option['sitekey'].'"></div><script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>" ></script></div>'; 
    
    // pega a mensagem criada pela validação
    $msg = get_option('rrcrecaptcha_return_form'); 
       
    // classe css error
    $class = $msg['type'] == "eror" ? ' error' : '';
    
    // corpo da mensagem
    $body_msg = '<div class="msgbody '.$class.'">'
            . '<div id="close">X</div>'
            . '<div class="msg">'.$msg['message'].'</div>'
            . '</div>';
    
    // inseri o botão e a div que conterá a mensagem
    $campos['submit_field']             = '<div class="form-submit">%1$s %2$s</div><div id="msg-grl">'.$body_msg.'</div>';
    
    // apos a apresentação da mensagem limpa o conteudo
    update_option('rrcrecaptcha_return_form', array());
    
    return $campos;
}




function pre_comment_check() {
    
    // se o campo não estiver preenchido retorna mensagem de erro    
    if ($_POST['author'] == "" || $_POST['author'] == NULL  ) {
        
        // gera a mensagem e salva 
        update_option('rrcrecaptcha_return_form', 
               array(
                   'type'   => 'error',
                   'message' => 'Campo Nome é de preenchimento obrigatório!'
               )
        );       
        
        // redireciona para o post atual
        wp_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
       
       return;
   }
   
   // se o campo não estiver preenchido retorna mensagem de erro    
   if ($_POST['email'] == "" || $_POST['email'] == NULL  ) {
       
       // verifaca se o e-mail esta com padrão de digitação 
       if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i",  $_POST['email'])){
           
           // gera a mensagem e salva 
           update_option('rrcrecaptcha_return_form', 
                   array(
                       'type'   => 'error',
                       'message' => 'O E-mail informação não está correto!'
                    )
           );
           
           // redireciona para o post atual
           wp_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
            
           return;
        }
        
        if(function_exists('checkdnsrr')){
            list (, $dominio)  = explode('@', $_POST['email'] );
            if ( !( checkdnsrr($dominio, 'MX') || checkdnsrr($dominio, 'A'))){
                
                // gera a mensagem e salva 
                update_option('rrcrecaptcha_return_form', 
                        array(
                            'type'   => 'error',
                            'message' => 'O E-mail informação não é válido!'
                        )
                );
                
                // redireciona para o post atual
                wp_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
                
                return;
                
            }                        
        }      
        
   }
   
   // se o campo não estiver preenchido retorna mensagem de erro    
   if ($_POST['comment'] == "" || $_POST['comment'] == NULL  ) {
        
        // gera a mensagem e salva 
        update_option('rrcrecaptcha_return_form', 
               array(
                   'type'   => 'error',
                   'message' => 'Campo Comentário é de preenchimento obrigatório!'
               )
        );       
        
        // redireciona para o post atual
       wp_safe_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
       
       return;
   }
   
   if($_POST['g-recaptcha-response'] == "" || $_POST['g-recaptcha-response'] == NULL){
       
       // gera a mensagem e salva 
       update_option('rrcrecaptcha_return_form', 
               array(
                   'type'   => 'error',
                   'message' => 'Não foi possivel validar o reCAPTCHA, tente novamente!'
               )
        );       
        
        // redireciona para o post atual
       wp_safe_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
       
       return;
       
   }else{
       
        $recaptcha = get_option('rrcrecaptcha_token');
       
        $url = "https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha['secretkey']."&response=".$_POST['g-recaptcha-response'];
 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, TRUE); 
        $curlData = curl_exec($curl);
 
        curl_close($curl);
 
        $res = json_decode($curlData, TRUE);
        if($res['success'] == 'true') 
            return TRUE;
        else
             // gera a mensagem e salva 
       update_option('rrcrecaptcha_return_form', 
               array(
                   'type'   => 'error',
                   'message' => 'Erro na validação do reCAPTCHA, tente novamente!'
               )
        );       
        
        // redireciona para o post atual
       wp_safe_redirect( get_permalink( (int)$_POST['comment_post_ID'] ) ); exit;
            return FALSE;
       
   }
   
   
}
 
add_action('pre_comment_on_post', 'pre_comment_check');