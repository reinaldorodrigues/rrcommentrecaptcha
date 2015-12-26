<div class="wrap">
     
 <?php 
    // pega a mensagem salva
    $messages = get_option('rrcrecaptcha_messages'); 
    if(isset($messages['tipo']) and $messages['mensagem']!=""){
        $tipo       = $messages['tipo'];
        $message    = $messages['mensagem'];
    } 
    
    if($tipo == 'erro'){         
        echo '<div id="message" class="error"><p>'.$message.'</p><br /></div>';            
    } $optionApp = get_option('rrcrecaptcha_token'); 
    if($tipo == 'sucesso'){ 
        echo '<div id="message" class="updated below-h2"><p>'.$message.'</p></div>';        
    }
    // atualiza a mensagem apagando o que existir
    update_option('rrcrecaptcha_messages', array()); 
    
   
    // pega os dados do facebook
    $optionApp = get_option('rrcrecaptcha_token'); 
    
    $return_sitekey     = '';
    $return_secretkey   = '';
    
    if(isset($optionApp['sitekey']) and $optionApp['secretkey']!=""){
        $return_sitekey      = $optionApp['sitekey'];
        $return_secretkey  = $optionApp['secretkey'];
    } 

    
    $url        = 'admin.php?page=rrcommentrecaptcha'
    
    ?>    
    
    <form method="post" action="<?php echo $url; ?>" id="addimage" name="addimage"  >
        <div id="poststuff">
            <div id="post-body" class="metabox-holder">
                <div id="post-body-content">
                    <h2>RR Comment Recaptcha</h2>
                    <div class="stuffbox" id="namediv" style="width:100%;">
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%;">
                                            <label for="sitekey">Site Key:</label>
                                        </th>
                                        <td>
                                            <input type="text" name="sitekey" id="sitekey" value="<?php echo $return_sitekey; ?>" size="50" />                                        
                                        </td>                                        
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">
                                            <label for="secretkey">Secret Key:</label>
                                        </th>
                                        <td>
                                            <input type="text" name="secretkey" id="appsecret" value="<?php echo $return_secretkey; ?>" size="50" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <input type="submit" name="saverrcrecaptcha" id="btnsave" value="Salvar" class="button-primary">                    
                </div>
            </div>
        </div>
    </form>
</div>

    
 

                    
