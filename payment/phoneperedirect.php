<?php include('../newapi/connection.php'); ?>
<?php  
    $secretkey = "";
    $orderId = $_GET["order"];
	$saltIndex =1;
	$sel_userQ =  $conn->prepare("Select * FROM tbl_tem_payment where tp_id = ? ");
    $sel_userQ->bindParam(1, $orderId);
    $sel_userQ->execute();
    $trans = $sel_userQ->fetch();
?>
<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My11option</title>
<style type="text/css">
@import url(https://fonts.googleapis.com/css?family=Roboto:300,300italic);
body,#bodyTable,#bodyCell{
height:100% !important;
margin:0;
padding:0;
width:100% !important;
}
table{
border-collapse:collapse;
}
img,a img{
border:0;
outline:none;
text-decoration:none;
}
h1,h2,h3,h4,h5,h6{
margin:0;
padding:0;
}
p{
margin:1em 0;
padding:0;
}
a{
word-wrap:break-word;
}
.ReadMsgBody{
width:100%;
}
.ExternalClass{
width:100%;
}
.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{
line-height:100%;
}
table,td{
mso-table-lspace:0pt;
mso-table-rspace:0pt;
}
#outlook a{
padding:0;
}
img{
-ms-interpolation-mode:bicubic;
}
body,table,td,p,a,li,blockquote{
-ms-text-size-adjust:100%;
-webkit-text-size-adjust:100%;
}
#bodyCell{
padding:20px;
}
.themezyImage{
vertical-align:bottom;
}
.themezyTextContent img{
height:auto !important;
}
body,#bodyTable{
background-color:#f5f5f5;
}
#bodyCell{
border-top:0;
}
#templateContainer{
border:0;
}
h1{
color:#ffffff !important;
display:block;
font-family:Arial,Helvetica;
font-size:40px;
font-style:normal;
font-weight:normal;
line-height:100%;
letter-spacing:normal;
margin:0;
text-align:center;
}
h2{
color:#fff !important;
display:block;
font-family:Arial,Helvetica;
font-size:24px;
font-style:normal;
font-weight:bold;
line-height:100%;
letter-spacing:normal;
margin:0px 0px 20px 0px;
text-align:center;
}
h3{
color:#78777d !important;
display:block;
font-family:Arial,Helvetica;
font-size:18px;
font-style:normal;
font-weight:normal;
line-height:125%;
letter-spacing:normal;
margin:0;
text-align:center;
}
h4{
color:#333 !important;
display:block;
font-family:Arial,Helvetica;
font-size:16px;
font-style:normal;
font-weight:bold;
line-height:125%;
letter-spacing:normal;
margin:0;
text-align:left;
}
h1,h2 {
font-family: 'Roboto', Arial, Helvetica;
font-weight: bold;
}
#templatePreheader{
background-color:#28282b;
border-top:0;
border-bottom:0;
}
.preheaderContainer .themezyTextContent,.preheaderContainer .themezyTextContent p{
color:#606060;
font-family:Arial,Helvetica;
font-size:11px;
line-height:125%;
text-align:left;
}
.preheaderContainer .themezyTextContent a{
color:#606060;
font-weight:normal;
text-decoration:underline;
}
#templateHeader{
background-color:#fff;
border-top:0;
border-bottom:1px solid #ddd;
}
.headerContainer .themezyTextContent,.headerContainer .themezyTextContent p{
color:#606060;
font-family:Arial,Helvetica;
font-size:15px;
line-height:150%;
text-align:left;
}
.headerContainer .themezyTextContent a{
color:#6DC6DD;
font-weight:normal;
text-decoration:underline;
}
#templateBody{
background-color:#ffffff;
border-top:0;
border-bottom:0;
}
.bodyContainer .themezyTextContent,.bodyContainer .themezyTextContent p{
color:#606060;
font-family:Arial,Helvetica;
font-size:15px;
line-height:150%;
text-align:left;
}
.bodyContainer .themezyTextContent a{
color:#60c1ae;
font-weight:normal;
text-decoration:none;
}
#templateFooter{
background-color:#ed1c24;
border-top:0;
border-bottom:0;
}
.footerContainer .themezyTextContent,.footerContainer .themezyTextContent p{
color:#fff;
font-family:Arial,Helvetica;
font-size:14px;
line-height:150%;
text-align:center;
margin-bottom:0px;
margin-top:5px;
}
.footerContainer .themezyTextContent a{
color:#8f8f95;
font-weight:normal;
text-decoration:underline;
}
@media only screen and (max-width: 480px){
body,table,td,p,a,li,blockquote{
-webkit-text-size-adjust:none !important;
}
body{
width:100% !important;
min-width:100% !important;
}
td[id=bodyCell]{
padding:10px !important;
}
table[class=themezyTextContentContainer]{
width:100% !important;
}
table[class=themezyBoxedTextContentContainer]{
width:100% !important;
}
table[class=mcpreview-image-uploader]{
width:100% !important;
display:none !important;
}
img[class=themezyImage]{
width:100% !important;
}
table[class=themezyImageGroupContentContainer]{
width:100% !important;
}
td[class=themezyImageGroupContent]{
padding:9px !important;
}
td[class=themezyImageGroupBlockInner]{
padding-bottom:0 !important;
padding-top:0 !important;
}
tbody[class=themezyImageGroupBlockOuter]{
padding-bottom:9px !important;
padding-top:9px !important;
}
table[class=themezyCaptionTopContent],table[class=themezyCaptionBottomContent]{
width:100% !important;
}
table[class=themezyCaptionLeftTextContentContainer],table[class=themezyCaptionRightTextContentContainer],table[class=themezyCaptionLeftImageContentContainer],table[class=themezyCaptionRightImageContentContainer],table[class=themezyImageCardLeftTextContentContainer],table[class=themezyImageCardRightTextContentContainer]{
width:100% !important;
}
td[class=themezyImageCardLeftImageContent],td[class=themezyImageCardRightImageContent]{
padding-right:18px !important;
padding-left:18px !important;
padding-bottom:0 !important;
}
td[class=themezyImageCardBottomImageContent]{
padding-bottom:9px !important;
}
td[class=themezyImageCardTopImageContent]{
padding-top:18px !important;
}
td[class=themezyImageCardLeftImageContent],td[class=themezyImageCardRightImageContent]{
padding-right:18px !important;
padding-left:18px !important;
padding-bottom:0 !important;
}
td[class=themezyImageCardBottomImageContent]{
padding-bottom:9px !important;
}
td[class=themezyImageCardTopImageContent]{
padding-top:18px !important;
}
table[class=themezyCaptionLeftContentOuter] td[class=themezyTextContent],table[class=themezyCaptionRightContentOuter] td[class=themezyTextContent]{
padding-top:9px !important;
}
td[class=themezyCaptionBlockInner] table[class=themezyCaptionTopContent]:last-child td[class=themezyTextContent]{
padding-top:18px !important;
}
td[class=themezyBoxedTextContentColumn]{
padding-left:18px !important;
padding-right:18px !important;
}
td[class=themezyTextContent]{
padding-right:18px !important;
padding-left:18px !important;
}
table[id=templateContainer],table[id=templatePreheader],table[id=templateHeader],table[id=templateBody],table[id=templateFooter]{
max-width:600px !important;
width:100% !important;
}
h1{
font-size:24px !important;
line-height:125% !important;
}
h2{
font-size:20px !important;
line-height:125% !important;
}
h3{
font-size:18px !important;
line-height:125% !important;
}
h4{
font-size:16px !important;
line-height:125% !important;
}
table[class=themezyBoxedTextContentContainer] td[class=themezyTextContent],td[class=themezyBoxedTextContentContainer] td[class=themezyTextContent] p{
font-size:18px !important;
line-height:125% !important;
}
table[id=templatePreheader]{
display:block !important;
}
td[class=preheaderContainer] td[class=themezyTextContent],td[class=preheaderContainer] td[class=themezyTextContent] p{
font-size:14px !important;
line-height:115% !important;
text-align: center !important;          
}
td[class=headerContainer] td[class=themezyTextContent],td[class=headerContainer] td[class=themezyTextContent] p{
font-size:18px !important;
line-height:125% !important;
}
td[class=bodyContainer] td[class=themezyTextContent],td[class=bodyContainer] td[class=themezyTextContent] p{
font-size:18px !important;
line-height:125% !important;
}
td[class=footerContainer] td[class=themezyTextContent],td[class=footerContainer] td[class=themezyTextContent] p{
font-size:14px !important;
line-height:115% !important;
}
td[class=footerContainer] a[class=utilityLink]{
display:block !important;
}
}
</style>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<center>
<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
<tr>
<td align="center" valign="top" id="bodyCell">
<!-- BEGIN TEMPLATE // -->
<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer">

<tr>
<td align="center" valign="top">
<!-- BEGIN HEADER // -->
<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader">
<tr>
<td valign="top" class="headerContainer">
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyImageBlock"  style="background:#ad1f13;">
<tbody class="themezyImageBlockOuter">
<tr>
<td valign="top" style="padding:9px" class="themezyImageBlockInner">
<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="themezyImageContentContainer">
<tbody><tr>
<td class="themezyImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;background:#ad1f13">
<a href="https://my11option.com/"><img align="center" alt="" src="https://my11option.com/frontassets/newd/images/logoh.png" width="317" style="max-width:317px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="themezyImage"></a>
</td>
</tr>
</tbody></table>
</td>
</tr>
</tbody>
</table>
</tr>
</table>
<!-- // END HEADER -->
</td>
</tr>
<tr>
<td align="center" valign="top">
<!-- BEGIN BODY // -->
<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody" style="border-radius: 5px;">
<tr>
<td valign="top" class="bodyContainer">
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyDividerBlock">
<tbody class="themezyDividerBlockOuter">
<tr>
<td class="themezyDividerBlockInner" style="padding: 50px 18px 0px;">
<table class="themezyDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td>
<span></span>
</td>
</tr>
</tbody></table>
</td>
</tr>
</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyTextBlock" >
<tbody class="themezyTextBlockOuter">
<tr>
<td valign="top" class="themezyTextBlockInner">
<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="themezyTextContentContainer">
<tbody><tr>
<td valign="top" class="themezyTextContent" style="padding-top:9px; padding-right: 18px; padding-bottom: 9px; padding-left: 18px; text-align:center;">
    
    <?php if(isset($trans['tp_status']) && $trans['tp_status']=='PAYMENT_SUCCESS')
    { ?>
    <p style="text-align:center">
    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72">
<g fill="none" stroke="#8EC343" stroke-width="2">
<circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;"></circle>
<path d="M17.417,37.778l9.93,9.909l25.444-25.393" style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path>
</g>
</svg>
</p>
<h4 style="text-align:center">Payment Successfull</h4>
<?php echo (isset($_GET['order']))?'<strong>Transaction Id : </strong> '.$_GET['order']:''; ?>
<p style="text-align:center">Please Close Browser and go to dashboard for successfull transaction.</p>

<?php }else  if(isset($trans['tp_status']) && $trans['tp_status']=='PAYMENT_ERROR')
    {  ?>
     <p style="text-align:center">
    <img src="cross.png" style="width:100px">
</p>
<h4 style="text-align:center">Payment Failed</h4>
<?php echo (isset($_GET['order']))?'<strong>Transaction Id : </strong> '.$_GET['order']:''; ?>
<p style="text-align:center">Please Try Again.</p>
    
    <?php } else 
    { 
    $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_status=\'PAYMENT_PENDING\' WHERE 1 AND tp_id='.$orderId;     
        $res = $conn->query($sqlSelect);
    ?>
     <p style="text-align:center">
    <img src="cross.png" style="width:100px">
</p>
<h4 style="text-align:center">Payment Pending</h4>
<?php echo (isset($_GET['order']))?'<strong>Transaction Id : </strong> '.$_GET['order']:''; ?>
<p style="text-align:center">Your Last Transaction is Pending for Verification. Please wait for a while. It may take up to 15 Minutes to reflect in your ID. IF Not, Please write us an Email.</p>
    
    <?php }?>
<hr>
<ul>
    <li style="text-align:left">After seccessful transaction, Amount will be added automatically in your account. Maximum Processing Time is 15 Minutes.</li>
    <li style="text-align:left">In Any Case, Amount is not reflecting in your Account, Please write us an Email.</li>
</ul>
  </td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyDividerBlock">
<tbody class="themezyDividerBlockOuter">
<tr>
<td class="themezyDividerBlockInner" style="padding: 50px 18px 0px;">
<table class="themezyDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td>
<span></span>
</td>
</tr>
</tbody></table>
</td>
</tr>
</tbody>
</table>

</tr>
</table>
<!-- // END BODY -->
</td>
</tr>
<tr>
<td align="center" valign="top">
<!-- BEGIN FOOTER // -->
<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateFooter"  style="background:#ad1f13;">
<tr>
<td valign="top" class="footerContainer" style="padding-bottom:9px;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyDividerBlock">
<tbody class="themezyDividerBlockOuter">
<tr>
<td class="themezyDividerBlockInner" style="padding: 15px 18px 0px;">
</td>
</tr>
</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyTextBlock" style="background:#ad1f13">
<tbody class="themezyTextBlockOuter" style="background:#ad1f13">
<tr>
<td valign="top" class="themezyTextBlockInner">
<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="themezyTextContentContainer">
<tbody><tr style="background:#ad1f13">
<td valign="top" class="themezyTextContent" style="font-size: 14px; padding-top:9px;background:#ad1f13; padding-right: 18px; padding-bottom: 9px; padding-left: 18px;">
<h2>Contact us</h2>
<p></p>
<p></p>
<p>Email: info@my11option.com</p>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="themezyDividerBlock">
<tbody class="themezyDividerBlockOuter">
<tr>
<td class="themezyDividerBlockInner" style="padding: 15px 18px 0px;">
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</table>
<!-- // END FOOTER -->
</td>
</tr>
</table>
<!-- // END TEMPLATE -->
</td>
</tr>
</table>
</center>
</body>
</html>
