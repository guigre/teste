<?php include("inc/topo.php"); ?>

	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				<i class="fa fa-mobile"></i> SMS
			</h1>
		</section>

        <!-- Main content -->
        <section class="content">

			<!-- Your Page Content Here -->
		  
			<!-- Horizontal Form -->
			<div class="box box-info">
				<div class="box-header with-border">
					<h3 class="box-title">Envio de SMS para grupo de clientes</h3>&nbsp;&nbsp;
					<small><font color="red">(Campos com (*) são obrigatórios.)</font></small>
                </div><!-- /.box-header -->
				<!-- form start -->
                <form class="form-horizontal">
					<div class="box-body">
						<div class="form-group">
							<label for="arquivoTelefones" class="col-sm-2 control-label">Arquivo de telefones<font color="red">*</font></label>
							<div class="col-xs-4">
								<input type="file" id="arquivoTelefones">
								<p class="help-block">Selecione aqui o arquivo com a lista de telefones que receberão a mensagem.<br>Ex. formato arquivo:<br>5199999999<br>5488888888</p>
							</div>
						</div>
						<div class="form-group">
							<label for="mensagemEnvio" class="col-sm-2 control-label">Mensagem<font color="red">*</font></label>
							<div class="col-xs-4">
								<textarea class="form-control" rows="4" id="mensagemEnvio" placeholder="Mensagem ..." maxlength="140" onkeyup="countChar(this)"></textarea>
								<div class="help-block" style="float:left;">Caracteres restantes:
									<div id="charNum" style="float:right;padding-left:5px;"></div>
								</div>
							</div>
						</div> <!-- /.form-group -->
					</div><!-- /.box-body -->
					<div class="box-footer">
						<button type="button" class="btn btn-primary" onclick="javascript:void(enviar())"><i class="fa fa-envelope-o"></i> Enviar</button>
						<div id="loading"></div>
					</div><!-- /.box-footer -->
				</form>
			</div><!-- /.box -->
			<div class="box">
				<div class="box-header">
					<h3 class="box-title">Retorno</h3>
				</div><!-- /.box-header -->
				<div class="box-body" id="divTabela">
				
                </div><!-- /.box-body -->
			</div><!-- /.box -->			  
        </section><!-- /.content -->
	</div><!-- /.content-wrapper -->
<script>

	function countChar(val){
		var len = val.value.length;
        if (len > 140) {
			val.value = val.value.substring(0, 140);
        }else{
			$('#charNum').text(140 - len);
        }
      };
	  
	function enviar(){
	
		$('#divTabela').html('');
		var mensagemEnvio = $('#mensagemEnvio').val();
		var arquivoTelefones = $('#arquivoTelefones').val();
		
		if(arquivoTelefones == ''){
			exibeErro('<p>Campo <b>(Arquivo de telefones)</b> Obrigatório!</p>');
			$('#arquivoTelefones').focus();
		}else if(mensagemEnvio == ''){
			exibeErro('<p>Campo <b>(Mensagem)</b> Obrigatório!</p>');
			$('#mensagemEnvio').focus();
		}else{
		
			var data = null;
			var header = null;
			var linha = null;
			var file = document.getElementById('arquivoTelefones').files[0];
			var reader = new FileReader();
			reader.readAsText(file, 'ISO-8859-1');
			reader.onload = function(event) {
			
				var csvData = event.target.result;
				data = $.csv.toArrays(csvData);
				
				if (data && data.length > 0) {
				
					var erro = false;
					
					for (i = 0; i < data.length; i++) { 
						
						if(data[i].toString().length !== 10 || isNaN(data[i].toString())){
							erro = true;
							i = data.length;
						}
					}
					
					if (erro === false){
					
						$.ajax({
							url: 'ajax/sms.php?acao=enviar',
							type: 'POST',
							timeout: 15000,
							dataType: 'json',
							data: {
								   'data'          : data,
								   'mensagemEnvio' : mensagemEnvio
							},
							beforeSend: function() {
								$('#loading').html('<p><img src=img/loading.gif></p>');
							},
							complete: function() {
								$('#loading').hide();
							},
							error: function(xhr, ajaxOptions, thrownError) {
								console.log(thrownError);
							},
							success: function(result) {
								
								var tabela = "<table id='tabelaTelefones' class='table table-bordered table-striped'>"
										+ "<thead>"
										+  "<tr>"
										+		"<th>Telefone</th>"
										+		"<th>Retorno</th>"
										+	  "</tr>"
										+	"</thead>"
										+	"<tbody>";
										
								var telefone;
								var retorno;
										
								for (i = 0; i < data.length; i++){
									telefone = data[i].toString();
									retorno = result.envioMsg[(i+1)];
									
									tabela += "<tr><td>" + telefone + "</td><td>" + retorno + "</td></tr>";
								}
								tabela += "</tbody></table>";
								
								$('#divTabela').html(tabela);
								$("#tabelaTelefones").DataTable();
							}
						});
					}else{
						exibeErro('<p>Layout do arquivo inválido. Ele deve conter 10 caracteres numéricos em todas as linhas.</p>');
						$('#arquivoTelefones').focus();	
					}
				}else{
					exibeErro('<p>Não há registros no arquivo.</p>');
					$('#arquivoTelefones').focus();
				}
			};
			reader.onerror = function() {
				exibeErro('<p>Não foi possível ler o arquivo ' + file.fileName + '</p>');
				$('#arquivoTelefones').focus();
			};
		}
	}
	
	$('#arquivoTelefones').focus();
	$('#charNum').text('140');
	
</script>	  

<?php include("inc/rodape.php"); ?>