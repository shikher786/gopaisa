<?php
error_reporting(1);
ini_set("memory_limit","10000M");
set_time_limit("0");
include "curl.php";
include "function_php.php";
$cookieFile = "./mysmartprice.txt";
$curl = new cURL(TRUE, $cookieFile);
$url = "http://www.mysmartprice.com/";
$resultfiles = $curl->get($url);
$fn_files = new multifunc();
//echo $resultfiles;
$fn_files->writeToFile("./msp1.html",$resultfiles);
$final_result=file_get_contents("./msp1.html", true);
$con = mysqli_connect("localhost","root","","mysmartprice");
$j=1;
if(mysqli_connect_errno()){
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if (preg_match('/\<div class=\"hdr\-size\__sub\-hdr\"\>\<\/div\>(.*?)Fitness\<\/span\>/is', $final_result, $matches)) {
$myresult = $matches[1];
//print_r($matches[1]);
while(preg_match('/<a.*?href=[\'|"](.*?)[\'|"].*?>.*?<\/span>\s*<\/a>/is',$myresult,$extracted_links)){
$myresult=$fn_files->after($extracted_links[0],$myresult);
echo '<br>';
//print_r($extracted_links[1]);
preg_match('/mysmartprice\.com\/(.*?)\//ims',$extracted_links[1],$names);
$str=$names[1];
//  print_r($names[1]);
$names=$curl->get($extracted_links[1]);
$fn_files->writeToFile($str.'.html',$names);
}
}
$mobile_result=file_get_contents("http://www.mysmartprice.com/mobile/",true);
//print_r($mobile_result);
preg_match('/\<div class=\"item\-grid\-item\__ttl\-wrpr\"\>\s*\<a href=\"(.*?)\"\s*class=\"item\-grid\-item\__ttl\"\>All\s*Mobile\s*Phones\<\/a\>/',$mobile_result,$link);
$all_mobile = "http://www.mysmartprice.com".$link[1]."#subcategory=mobile";
//echo $all_mobile;
$link = $curl->get($all_mobile);
//echo $link;
$fn_files->writeToFile("all".'.html',$link);
//$all_mobile_result = file_get_contents("http://www.mysmartprice.com/mobile/pricelist/mobile-price-list-in-india.html#subcategory=mobile",true);
//echo $all_mobile_result;
$next_url = "http://www.mysmartprice.com/mobile/pricelist/pages/mobile-price-list-in-india-2.html#subcategory=mobile";



if(preg_match('/\<div class=\'js\-fltrs\-apld\-cler\'\>CLEAR\s*ALL\<\/div>(.*?)Lenovo\s*Vibe\s*P1m\s*\<\/a\>/ims',$link,$phones)){
//echo "match found";
print_r($phones[1]);
$my_result1=$phones[1];

do{
	sleep(2);
	$j=0;
$my_result1=$curl->get($my_result1);


while(preg_match('/<a class="prdct-item__img-wrpr"\s*href=[\'|"](.*?)[\'|"].*?>/ims',$my_result1,$individual_links)){
$my_result1=$fn_files->after($individual_links[0],$my_result1);
//echo ($individual_links[0]);
//print_r($individual_links[1]);
//echo '<br>';
//exit;
preg_match('/.*\/(.*)/ims',$individual_links[1],$mob);
//echo($mob[1]);
//echo '<br>';
$variable = $mob[1];
$variable1 = $curl->get($individual_links[1]);
$fn_files->writeToFile($variable.'.html',$variable1);


preg_match('/data\-jdavailable=\"N\"\>(.*?)\<\/h1\>/',$variable1,$title);
//echo ($title[1]);
//echo '<br>';
$titlev = $con->escape_string($title[1]);
//echo '<br>';
preg_match('/\<div class=\"prdct\-dtl\__box\-best\-prc\"\>(.*?)\<\/div\>/',$variable1,$price);
//echo($price[1]);
$pricev= $con->escape_string($price[1]);
echo'<br>';
preg_match('/\<h3 class=\"sctn\__ttl\s*\"\>(.*?)\<h2 class=\"sctn\__hdr\s*prc\-grid\__hdr\s*sctn\__ttl\s*">/',$variable1,$details);
//echo ($details[1]);
//echo "***********************************************************************************************************************************************************";
$detailsv= $con->escape_string($details[1]);
//$not_required= array("<div>","</div>","<li>","</li>","<h3>","</h3>","<h4>","</h4>");
//$detailsv_1=str_replace($not_required,"",$detailsv);
echo '<br>';
$sql = "INSERT INTO msp( `Title_of_Phone` , `Price` , `Description` , `Status` )
VALUES ('$titlev', '$pricev', '$detailsv', '1')";
//echo $sql;
$result = $con->query($sql);
$sql1 = "select * from msp";
$result1 = $con->query($sql1);
if($result1->num_rows >0){
	while($row = $result1->fetch_assoc()) {
		//echo "Title of the Phone" . $row["Title_of_Phone"] . "Price:" . $row["Price"] . "Description" . $row["Description"]. "Status:" . $row["Status"] . "<br>";
	}
}
if(preg_match('/<a class=\'pgntn__item\'\s*href=[\'|"](.*?)[\'|"].*?<\/a>/ims',$link,$next)&&($j<2)){
//echo ($next[1]);
$my_result1=$next[1];
$j++;
}else{
$next_url = "";
//exit;
}

}

}while($link != " ");
}
$con->close();
?>
