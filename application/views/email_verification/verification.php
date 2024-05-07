<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_NAME; ?></title>
</head>

<body style="padding: 0px; margin: 0px;">
	
<div>
	<div style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136);text-align: center;background: #0e2141;padding: 9px 2px;">
													<div style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)"><a href="<?php echo HTTP_PATH; ?>" style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(0,160,251);text-decoration:none;font-weight:bold" target="_blank"><img src="<?php echo APP_URL; ?>img/logo.png" alt="<?php echo APP_NAME; ?>" height="75" width="250" style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136);outline:none;border:none"></a></div>
												</div>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136);font-size:12px;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:normal;text-align:start;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;margin: 0;padding: 0;">
		<tbody style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
			<tr style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
				<td style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
					<table align="center" border="0" cellpadding="0" cellspacing="0" style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136);border-collapse:collapse;width:100%">
						<tbody style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
							 
							<tr style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
								<td style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
									<div style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(255,255,255);background-color: #ffffff;border-top-left-radius:3px;border-top-right-radius:3px;border-bottom-right-radius:3px;border-bottom-left-radius:3px;padding:20px 27px 40px;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color:rgb(226,228,231);">
										<table align="center" border="0" cellpadding="0" cellspacing="0" style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136);text-align:center">
											<tbody style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">

												<?php if(!empty($name)){ ?>
												<tr style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)">
												<td style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)"><h1 style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color: #0e2141;line-height:1;margin:35px 0px 25px;font-size:28px;font-weight:normal;">Hello <?php echo $name; ?></h1></td>

												</tr>

												<?php } ?>


												<tr style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)"><td style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color:rgb(136,136,136)"><p style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color: #333;font-size:16px;line-height:1.5;margin:0px 0px 40px;"><?php echo $message; ?></p></td></tr>
												
												 
												 </tbody>
										</table>
									</div>
								</td>
							</tr>
						
							
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
		<div style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;background-color: #000;text-align: center;">
								<div style="font-family:Proxima-nova,'Helvetica Neue',Helvetica,Arial,sans-serif;color: rgb(183, 183, 183);padding: 15px;">© <?php echo date("Y")?> <?php echo APP_NAME; ?>, All Rights Reserved.</div>
							</div>
	</div>	
	


</body>
</html>
